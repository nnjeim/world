<?php

namespace Nnjeim\World\Http\Requests\Geolocate;

use Nnjeim\World\Http\Requests\BaseRequest;

class IndexRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ip' => 'sometimes|ip',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'ip.ip' => 'The provided IP address is not valid.',
        ];
    }
}
