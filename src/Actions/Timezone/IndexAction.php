<?php

namespace Nnjeim\World\Actions\Timezone;

use Nnjeim\World\Actions\Timezone\Queries\IndexQuery;
use Nnjeim\World\Actions\Timezone\Transformers\IndexTransformer;

use Nnjeim\World\Actions\{BaseAction, ActionInterface};
use Illuminate\Support\Facades\Cache;

class IndexAction  extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'timezones';
	protected string $attribute = 'timezone';
	protected array $availableFields = [
		'id',
		'name',
		'country_id',
		'country',
	];
	protected array $fields = [
		'id',
		'name',
	];
	protected array $relations = [
		'country',
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
		$timezones = Cache::rememberForever(
			$this->cacheKey,
			fn() => $this->transform((new IndexQuery($this->wheres, $this->with))(), $this->fields)
		);
		/*-- Response --*/
		return $this->formResponse($timezones);
	}
}
