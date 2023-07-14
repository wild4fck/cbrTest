<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ApiFormRequest;

/**
 * @property \Carbon\Carbon $date
 * @property string $currency_code
 * @property ?string $base_currency_code
 */
class ComparisonWithYesterdayRequest extends FormRequest
{
    use ApiFormRequest;
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'currency_code' => ['required', 'string'],
            'base_currency_code' => ['nullable', 'string'],
        ];
    }
}
