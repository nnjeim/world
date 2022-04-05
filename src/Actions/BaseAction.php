<?php

namespace Nnjeim\World\Actions;

use Illuminate\Support\Collection;
use Nnjeim\World\Actions\Traits\IndexFieldsTrait;

class BaseAction
{
	use IndexFieldsTrait;

	public bool $success = true;

	public string $message;

	public Collection $data;

	public array $errors = [];

	public int $statusCode = 200;

	protected string $cacheTag;

	protected string $cacheKey;

	protected string $attribute;

	/**
	 * @param Collection $data
	 * @return $this
	 */
	protected function formResponse(Collection $data): self
	{
		$this->success = count($data) > 0;
		$this->message = $this->attributeMessage($this->attribute, true);
		$this->data = $data;
		$this->statusCode = $this->success ? 200 : 404;

		return $this;
	}

	/**
	 * @param string $attribute
	 * @param bool $plural
	 * @return string
	 */
	protected function attributeMessage(string $attribute, bool $plural = false): string
	{
		return trans_choice("world::response.attributes.$attribute", (int) $plural + 1);
	}

	/**
	 * @param  string  $record
	 * @return $this
	 */
	protected function recordNotFound(string $record = 'record'): self
	{
		$this->success = false;

		$this->errors = [
			'message' => trans('world::response.errors.record_not_found', [
				'attribute' => trans('world::response.' . $record . '.singular'),
			]),
		];

		$this->statusCode = 404;

		return $this;
	}
}
