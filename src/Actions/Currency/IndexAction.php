<?php

namespace Nnjeim\World\Actions\Currency;

use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\Currency\Queries\IndexQuery;
use Nnjeim\World\Actions\Currency\Transformers\IndexTransformer;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'currencies';

	protected string $attribute = 'currency';

	protected array $defaultFields = [
		'id',
		'name',
	];

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
		] = $args + [
			'fields' => null,
			'filters' => null,
		];

		$this->validateArguments($fields, $filters);

		// cache
		$currencies = Cache::rememberForever(
			$this->cacheKey,
			fn () => $this->transform(
				(new IndexQuery($this->validatedFilters, $this->validatedRelations))(),
				array_merge($this->validatedFields, $this->validatedRelations)
			)
		);
		// response
		return $this->formResponse($currencies);
	}
}
