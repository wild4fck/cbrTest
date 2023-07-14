<?php

namespace App\Http\Requests\Traits;

use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
trait ApiFormRequest
{
    /**
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ResponseHelper::makeResponse(
            ResponseAlias::HTTP_UNPROCESSABLE_ENTITY,
            message: 'Invalid data send',
            errors: $validator->errors()->getMessages(),
        ));
    }
}
