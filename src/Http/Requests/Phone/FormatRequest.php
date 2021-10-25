<?php

namespace Nnjeim\World\Http\Requests\Phone;

use Nnjeim\World\Http\Requests\BaseRequest;

class FormatRequest extends BaseRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'number' => 'required',
			'phone_code' => 'sometimes',
		];
	}
}
