<?php

namespace Nnjeim\World\Http\Requests\State;

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
			'fields' => 'sometimes',
			'filters' => 'sometimes',
		];
	}
}
