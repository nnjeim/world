<?php

namespace Nnjeim\World\Actions\City;

use Nnjeim\World\Actions\City\Queries\IndexQuery;
use Nnjeim\World\Actions\City\Transformers\IndexTransformer;

use Nnjeim\World\Actions\{BaseAction, ActionInterface};
use Illuminate\Support\Facades\Cache;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'cities';
	protected string $attribute = 'city';
	protected array $availableFields = [
		'id',
		'name',
		'state_id',
		'country_id',
		'country',
		'state',
	];
	protected array $fields = [
		'id',
		'name',
	];
	protected array $relations = [
		'country',
		'state',
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
		$cities = Cache::rememberForever(
			$this->cacheKey,
			fn() => $this->transform((new IndexQuery($this->wheres, $this->with))(), $this->fields)
		);
		/*-- Response --*/
		return $this->formResponse($cities);
	}
}
