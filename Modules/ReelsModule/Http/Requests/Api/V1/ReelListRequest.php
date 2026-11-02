<?php

namespace Modules\ReelsModule\Http\Requests\Api\V1;

use App\CentralLogics\Helpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReelListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => 'nullable|integer|exists:restaurants,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:1',
        ];
    }

    public function withValidator($validator): void
    {
        //
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(['errors' => Helpers::error_processor($validator)], 403);

        throw new ValidationException($validator, $response);
    }
}
