<?php

namespace Nnjeim\World\Actions\Timezone;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\Timezone\Queries\IndexQuery;
use Nnjeim\World\Actions\Timezone\Transformers\IndexTransformer;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'timezones';

	protected string $attribute = 'timezone';

	protected array $defaultFields = [
		'id',
		'name',
	];

	protected array $availableFields = [
		'id',
		'name',
		'country_id',
	];

	protected array $availableRelations = [
		'country',
	];

	/**
	 * @param  array  $args
	 * @return $this
	 */
	public function execute(array $args = []): self
	{
		[
			'fields' => $fields,
			'filters' => $filters,
			'search' => $search,
		] = $args + [
			'fields' => null,
			'filters' => null,
			'search' => null,
		];

		$this->validateArguments($fields, $filters);

		// cache
		$timezones = $search === null
			? Cache::rememberForever(
				$this->cacheKey,
				fn () => $this->indexQuery($search)
			)
			: $this->indexQuery($search);

		// response
		return $this->formResponse($timezones);
	}

	/**
	 * @param  string|null  $search
	 * @return Collection
	 */
	private function indexQuery(?string $search = null): Collection
	{
		return $this->transform(
			(new IndexQuery($this->validatedFilters, $this->validatedRelations, $search))(),
			array_merge($this->validatedFields, $this->validatedRelations)
		);
	}
}
