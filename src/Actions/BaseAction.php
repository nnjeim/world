<?php

namespace Nnjeim\World\Actions;

use Illuminate\Support\Collection;
use Nnjeim\World\Actions\Traits\IndexFieldsTrait;
use Nnjeim\World\Http\Response\ResponseBuilder;

class BaseAction
{
	use IndexFieldsTrait;

	public bool $success = true;

	public Collection $data;

	protected string $cacheTag;

	protected string $cacheKey;

	protected string $attribute;

	/**
	 * @return ResponseBuilder
	 */
	public function withResponse(): ResponseBuilder
	{
		return ResponseBuilder::make()
			->setSuccess($this->success)
			->setAttributeMessage($this->attribute, true)
			->setData($this->data)
			->setStatusCode(
				$this->success
					? ResponseBuilder::HTTP_OK
					: ResponseBuilder::HTTP_NOT_FOUND
			);
	}
}
