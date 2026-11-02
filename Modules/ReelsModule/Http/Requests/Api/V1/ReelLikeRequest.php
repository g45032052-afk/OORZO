<?php

namespace Modules\ReelsModule\Http\Requests\Api\V1;

use App\CentralLogics\Helpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReelLikeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user('api');
    }

    public function rules(): array
    {
        return [
            'reel_id' => 'required|integer|exists:reels,id',
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
