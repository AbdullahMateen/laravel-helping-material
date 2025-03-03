<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Traits\Api;

use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function response($response_code, $message, $data = [], $errors = [], $source = null)
    {
        $response = [];

        switch ($response_code) {
            case Response::HTTP_OK:
                $response = $this->responseOK($response_code, $message, $data, $errors, $source);
                break;
            case Response::HTTP_BAD_REQUEST:
                $response = $this->responseBadRequest($response_code, $message, $data, $errors, $source);
                break;
            case Response::HTTP_NOT_FOUND:
                $response = $this->responseNotFound($response_code, $message, $data, $errors, $source);
                break;
            case Response::HTTP_UNPROCESSABLE_ENTITY:
                $response = $this->responseUnprocessableEntity($response_code, $message, $data, $errors, $source);
                break;
            case Response::HTTP_INTERNAL_SERVER_ERROR:
                $response = $this->responseInternalServerError($response_code, $message, $data, $errors, $source);
                break;
            default:
                $response = $this->somethingWentWrong($response_code, $message, $data, $errors, $source);
                break;
        }


        return response()->json($response, $response_code);//, [], JSON_NUMERIC_CHECK
    }

    private function responseOK($response_code, $message, $data, $errors = [], $source = null)
    {
        return $this->responseBody(true, $response_code, $message, $data, $errors, $source);
    }

    private function responseBadRequest($response_code, $message, $data, $errors = [], $source = null)
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors, $source);
    }

    private function responseNotFound($response_code, $message, $data, $errors = [], $source = null)
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors, $source);
    }

    private function responseUnprocessableEntity($response_code, $message, $data, $errors = [], $source = null)
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors, $source);
    }

    private function responseInternalServerError($response_code, $message, $data, $errors = [], $source = null)
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors, $source);
    }

    private function somethingWentWrong($response_code, $message, $data, $errors = [], $source = null)
    {
        return $this->responseBody(false, $response_code, $message, $data, $errors, $source);
    }

    private function responseBody(bool $success, $response_code, $message, $data, $errors, $source)
    {
        return $response = $this->prepareResponseData($success, $response_code, $message, $data, $errors, $source);
    }

    private function prepareResponseData(bool $success, $response_code, $message, $data, $errors, $source)
    {
        $response                = [];
        $response['code']        = $response_code;
        $response['success']     = $success ? 1 : 0;
        $response['message']     = $this->responseStatusText($response_code);
        $response['description'] = $response_code == Response::HTTP_UNPROCESSABLE_ENTITY
            ? implode("\n", array_unique(array_flatten($errors)))
            : $message;
        $response['data']        = config('lhm.api.response_keys.snake_case') ? array_keys_to_snake_case($data) : $data;

        if ($response_code !== Response::HTTP_OK) {
            $response['exception'] = $source;
            $response['errors']    = count($errors) || $response_code == Response::HTTP_UNPROCESSABLE_ENTITY
                ? $errors // implode("\n", array_unique(array_flatten($errors)))
                : $message;
        }

        return $response;

        //        $response = [];
        //        $response['success'] = $success ? 1 : 0;
        //        $response['response_code'] = $response_code;
        //        $response['message'] = $response_code == Response::HTTP_UNPROCESSABLE_ENTITY
        //            ? implode("\n", array_unique(array_flatten($errors)))
        //            : $message;
        //        $response['data'] = $data;
        //
        //        return $response;
    }

    private function responseStatusText($code)
    {
        $statusTexts        = Response::$statusTexts;
        $statusTexts['419'] = 'Token Mismatch';
        $statusTexts['422'] = 'Invalid data provided.';

        return $statusTexts[$code];
    }
}
