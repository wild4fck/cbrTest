<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * JSON ответ для фронта
     *
     * @param int  $status  Статус ответа
     * @param null|mixed  $data  Данные полученные в результате обращения
     * @param null|string  $message  Сообщение о результате обращения
     * @param array|null  $errors  Список ошибок, возникших во время обращения
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function makeResponse(
        int $status,
        mixed $data = null,
        ?string $message = null,
        ?array $errors = null
    ): JsonResponse {
        $response = [];

        if (isset($message)) {
            $response['message'] = $message;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json(
            $response,
            $status,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Charset' => 'utf-8',
            ],
            JSON_UNESCAPED_UNICODE
        );
    }
}
