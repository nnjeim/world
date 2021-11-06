<?php

namespace Nnjeim\World\Actions\Currency;

use Nnjeim\World\Actions\Currency\Queries\IndexQuery;
use Nnjeim\World\Actions\Currency\Transformers\IndexTransformer;

use Nnjeim\World\Actions\{BaseAction, ActionInterface};
use Illuminate\Support\Facades\Cache;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'currencies';
	protected string $attribute = 'currency';
	protected array $availableFields = [
		'id',
		'name',
		'country_id',
		'code',
		'precision',
		'symbol',
		'symbol_native',
		'symbol_first',
		'decimal_mark',
		'thousands_separator',
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
	public function execute(array $args = []): self
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
		$currencies = Cache::rememberForever(
			$this->cacheKey,
			fn() => $this->transform((new IndexQuery($this->wheres, $this->with))(), $this->fields)
		);
		/*-- Response --*/
		return $this->formResponse($currencies);
	}
}
