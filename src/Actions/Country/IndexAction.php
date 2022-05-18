<?php

namespace Nnjeim\World\Actions\Country;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\Country\Queries\IndexQuery;
use Nnjeim\World\Actions\Country\Transformers\IndexTransformer;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $module = 'countries';

	protected string $cacheTag = 'countries';

	protected string $attribute = 'country';

	protected array $defaultFields = [
		'id',
		'name',
	];

	protected array $availableFields = [
		'id',
		'iso2',
		'iso3',
		'name',
		'phone_code',
		'region',
		'subregion',
	];

	protected array $availableRelations = [
		'states',
		'cities',
		'timezones',
		'currency',
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
		$countries = $search === null
			? Cache::rememberForever(
				$this->cacheKey,
				fn () => $this->indexQuery($search)
			)
			: $this->indexQuery($search);

		// response
		return $this->formResponse($countries);
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
