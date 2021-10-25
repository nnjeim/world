<?php

namespace Nnjeim\World\Actions;

use Illuminate\Support\Collection;

class BaseAction
{
	public bool $success = true;
	public string $message;
	public Collection $data;
	public array $errors = [];
	public int $statusCode = 200;
	protected string $cacheTag;
	protected string $attribute;
	protected array $availableFields = ['id', 'name'];
	protected array $fields = ['id', 'name'];
	protected array $relations = [];
	protected array $wheres = [];
	protected array $with = [];
	protected string $cacheKey;

	protected function formCacheKey(): void
	{
		sort($this->fields);
		sort($this->with);
		$cacheKey =  implode('_', array_unique(array_merge([$this->cacheTag], $this->fields, $this->with)));
		foreach ($this->wheres as $where) {
			$cacheKey .= '_' . implode('', $where);
		}
		$cacheKey .= '_' . config('app.locale');
		$this->cacheKey = $cacheKey;
	}
	/**
	 * @param  string|null  $fields
	 */
	protected function formFields(string $fields = null): void
	{
		if ($fields !== null) {
			$this->fields = array_merge(
				$this->fields,
				array_values(
					array_intersect(
						$this->availableFields,
						explode(',', strip_tags(str_replace(' ', '', $fields)))
					)
				)
			);
		}
	}

	/**
	 * @param  array|null  $filters
	 */
	protected function formFilters(?array $filters = null): void
	{
		if ($filters !== null) {
			foreach ($filters as $key => $value) {
				if (in_array($key, $this->availableFields)) {
					$this->wheres[] = [$key, '=', $value];
				}
			}
		}
	}

	protected function formWith(): void
	{
		foreach($this->relations as $relation) {
			if (in_array($relation, $this->fields)) {
				$this->with += [$relation];
			}
		}
	}

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
				'attribute' => trans('world::response.' . $record . '.singular')
			])
		];

		$this->statusCode = 404;

		return $this;
	}
}
