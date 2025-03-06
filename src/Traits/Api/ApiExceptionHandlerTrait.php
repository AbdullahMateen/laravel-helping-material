<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Exceptions\BackedEnumCaseNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
//use Tymon\JWTAuth\Exceptions\JWTException;
//use Tymon\JWTAuth\Exceptions\TokenExpiredException;
//use Tymon\JWTAuth\Exceptions\TokenInvalidException;

trait ApiExceptionHandlerTrait
{
    private $status_code = null;
    private $message = '';
    private $errors = [];
    private $source = null;

    public function handleApiException($request, Throwable $exception)
    {
        $this->errors = isset($exception->errorBag) && $exception->errorBag !== "default" ? $exception->errorBag : [];
        $this->message = $exception->getMessage() ?? '';
        $this->status_code = $exception->status ?? null;

//        if ($exception instanceof TokenExpiredException) {
//            return $this->response(Response::HTTP_BAD_REQUEST, 'Auth token is expired', []);
//        } else if ($exception instanceof TokenInvalidException) {
//            return $this->response(Response::HTTP_BAD_REQUEST, 'Auth token is invalid', []);
//        } else if ($exception instanceof JWTException) {
//            return $this->response(Response::HTTP_BAD_REQUEST, 'Auth token not found', []);
//        }
        if ($exception instanceof ModelNotFoundException) {
            return response()->response(Response::HTTP_NOT_FOUND, 'Record not found', []);
        }

        $exception = $this->prepareException($exception);

        if ($exception instanceof HttpResponseException) {
            $exception = $exception->getResponse();
        }

        if ($exception instanceof \Error) {
            $this->message = 'The requested resource was not found.';
        }

        if ($exception instanceof NotFoundHttpException) {
            // $exception = new NotFoundHttpException('The requested resource was not found.', $exception);
            // return response()->response(Response::HTTP_NOT_FOUND, 'The requested page could not be found. Please check the URL or try again later.', []);
            $this->message = 'The requested resource was not found.';
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }

        return $this->customApiResponse($exception);
    }

    protected function prepareException(Throwable $e)
    {
        return match (true) {
            $e instanceof BackedEnumCaseNotFoundException            => new NotFoundHttpException($e->getMessage(), $e),
            $e instanceof ModelNotFoundException                     => new NotFoundHttpException($e->getMessage(), $e),
            $e instanceof AuthorizationException && $e->hasStatus()  => new HttpException(
                $e->status(), $e->response()?->message() ?: (\Illuminate\Http\Response::$statusTexts[$e->status()] ?? 'Whoops, looks like something went wrong.'), $e
            ),
            $e instanceof AuthorizationException && !$e->hasStatus() => new AccessDeniedHttpException($e->getMessage(), $e),
            $e instanceof TokenMismatchException                     => new HttpException(419, $e->getMessage(), $e),
            $e instanceof RequestExceptionInterface                  => new BadRequestHttpException('Bad request.', $e),
            $e instanceof RecordsNotFoundException                   => new NotFoundHttpException('Not found.', $e),
            default                                                  => $e,
        };
    }

    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        return response()->json(['message' => $exception->getMessage()], 401);
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return response()->json([
            'message' => $e->getMessage(),
            'errors'  => $e->errors(),
        ], $e->status);
    }

    private function customApiResponse($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        $response = [];

        switch ($statusCode) {
            case 401:
                $response['message'] = 'Unauthorized';
                break;
            case 403:
                $response['message'] = 'Forbidden';
                break;
            case 404:
                $response['message'] = 'Not Found';
                break;
            case 405:
                $response['message'] = 'Method Not Allowed';
                break;
            case 422:
                $response['message'] = $exception->original['message'];
                $response['errors'] = $exception->original['errors'];
                break;
            default:
                $response['message'] = ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage();
                break;
        }

        $response['status'] = $this->status_code ?? $statusCode;
        $response['message'] = $response['status'] == 500 ? $response['message'] : $this->message;

        $this->status_code = $response['status'];
        $this->message = $response['message'];
        $this->errors = (isset($this->errors) && !empty($this->errors)) ? $this->errors : $response['errors'] ?? [];
        $this->source = exception_response($exception);

        return response()->response($this->status_code, $this->message, [], $this->errors ?? [], $this->source);
    }
}
