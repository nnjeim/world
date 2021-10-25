<?php

namespace Nnjeim\World\Actions\Country;

use Nnjeim\World\Actions\Country\Queries\IndexQuery;
use Nnjeim\World\Actions\Country\Transformers\IndexTransformer;

use Nnjeim\World\Actions\{BaseAction, ActionInterface};
use Illuminate\Support\Facades\Cache;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'countries';
	protected string $attribute = 'country';
	protected array $availableFields = [
		'id',
		'iso2',
		'iso3',
		'name',
		'phone_code',
		'dialing_pattern',
		'region',
		'sub_region',
		'states',
		'cities',
		'timezones',
		'currency',
	];
	protected array $fields = [
		'id',
		'name'
	];
	protected array $relations = [
		'states',
		'cities',
		'timezones',
		'currency'
	];

	/**
	 * @param  array  $args
	 * @return $this
	 */
	public function execute(array $args): self
	{
		[
			'fields' => $fields,
			'filters' => $filters,
		] = $args + [
			'fields' => null,
			'filters' => null,
		];

		$this->formFields($fields);
		$this->formFilters($filters);
		$this->formWith();
		$this->formCacheKey();

		/*-- cache --*/
		$countries = Cache::rememberForever(
			$this->cacheKey,
			fn() => $this->transform((new IndexQuery($this->wheres, $this->with))(), $this->fields)
		);
		/*-- Response --*/
		return $this->formResponse($countries);
	}
}
