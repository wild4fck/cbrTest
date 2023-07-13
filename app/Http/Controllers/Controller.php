<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    /**
     * JSON response
     *
     * @param int  $status  Статус ответа
     * @param null|mixed  $data  Данные полученные в результате обращения
     * @param null|string  $message  Сообщение о результате обращения
     * @param array|null  $errors  Список ошибок, возникших во время обращения
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function makeResponse(
        int $status,
        mixed $data = null,
        ?string $message = null,
        ?array $errors = null
    ): JsonResponse {
        return ResponseHelper::makeResponse($status, $data, $message, $errors);
    }
    
    /**
     * @param string  $message
     * @param null  $errors
     *
     * @param null  $data
     * @param int  $errorCode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        string $message,
        mixed $errors = null,
        mixed $data = null,
        int $errorCode = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return $this->makeResponse($errorCode, $data, $message, $errors);
    }
    
    /**
     * @param string  $message
     * @param mixed  $data
     * @param mixed  $errors
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function failureResponse(string $message, mixed $errors = null, mixed $data = null): JsonResponse
    {
        return $this->makeResponse(ResponseAlias::HTTP_BAD_REQUEST, $data, $message, $errors);
    }
    
    /**
     * @param mixed  $data
     * @param null|string  $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(mixed $data = null, ?string $message = null): JsonResponse
    {
        return $this->makeResponse(ResponseAlias::HTTP_OK, $data, $message);
    }
}
