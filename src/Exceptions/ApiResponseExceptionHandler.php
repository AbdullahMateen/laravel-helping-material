<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Exceptions;

use AbdullahMateen\LaravelHelpingMaterial\Traits\Api\ApiExceptionHandlerTrait;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Api\ApiResponseTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ValueError;

class ApiResponseExceptionHandler
{
    use ApiResponseTrait, ApiExceptionHandlerTrait;

    public static function handle($exceptions)
    {
        $exceptions->render(function (ValueError $error, \Illuminate\Http\Request $request) {
            return (new self)->handleErrorValue($request, $error);
        });
        $exceptions->render(function (\Exception $exception, \Illuminate\Http\Request $request) {
            if ($request->wantsJson()) {
                return (new self)->handleApiException($request, $exception);
            }

            return false;
        });
        $exceptions->render(function (\Throwable $exception, \Illuminate\Http\Request $request) {
            if ($request->wantsJson()) {
                return (new self)->handleApiException($request, $exception);
            }

            return false;
        });
    }

    public function handleErrorValue(Request $request, ValueError $error) {

        return response()->response(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $error->getMessage()
        );
    }

}
