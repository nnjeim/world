<?php

namespace Nnjeim\World\Actions\Traits;

trait IndexFieldsTrait
{
	/**
	 * define the default fields ex. id, name.
	 * @var array
	 */
	protected array $defaultFields = [];
	/**
	 * define the available fields.
	 * @var array
	 */
	protected array $availableFields = [];
	/**
	 * define the available relationships.
	 * @var array
	 */
	protected array $availableRelations = [];

	protected array $validatedFields = [];

	protected array $validatedFilters = [];

	protected array $validatedRelations = [];

	/**
	 * @param  string|null  $fields
	 * @param  array|null  $filters
	 * @return void
	 */
	protected function validateArguments(?string $fields = null, ?array $filters = null)
	{
		$this
			->validateFields($fields)
			->validateFilters($filters)
			->validateRelations($fields)
			->computeCacheKey();
	}

	/**
	 * Helper to validate the selected fields.
	 * @param  string|null  $fields
	 * @return $this
	 */
	protected function validateFields(?string $fields = null): self
	{
		if ($fields === null) {

			$this->validatedFields = $this->defaultFields;

			return $this;
		}

		$this->validatedFields = array_merge(
			$this->defaultFields,
			array_values(
				array_intersect(
					$this->availableFields,
					explode(',', strip_tags(str_replace(' ', '', $fields)))
				)
			)
		);

		return $this;
	}

	/**
	 * Helper to validate the filters array.
	 * @param  array|null  $filters
	 * @return $this
	 */
	protected function validateFilters(?array $filters = null): self
	{
		if ($filters !== null) {
			foreach ($filters as $key => $value) {
				if (in_array($key, $this->availableFields)) {
					$this->validatedFilters[] = [$key, '=', $value];
				}
			}
		}

		return $this;
	}

	/**
	 * Helper to validate the relationships array.
	 * @param  string|null  $fields
	 * @return $this
	 */
	protected function validateRelations(?string $fields = null): self
	{
		if ($fields !== null) {
			foreach ($this->availableRelations as $relation) {
				if (in_array($relation, explode(',', strip_tags(str_replace(' ', '', $fields))))) {
					$this->validatedRelations[] = $relation;
				}
			}
		}

		return $this;
	}

	/**
	 * Helper to dynamically form a cache key based on fields and filters.
	 * @return $this
	 */
	protected function computeCacheKey(): self
	{
		sort($this->validatedFields);
		sort($this->validatedRelations);
		$cacheKey = implode('_', array_unique(array_merge([$this->cacheTag], $this->validatedFields, $this->validatedRelations)));
		foreach ($this->validatedFilters as $where) {
			$cacheKey .= '_' . implode('', $where);
		}
		$cacheKey .= '_' . config('app.locale');
		$this->cacheKey = $cacheKey;

		return $this;
	}
}
