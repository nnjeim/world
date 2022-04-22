<?php

namespace Nnjeim\World\Actions\City;

use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\City\Queries\IndexQuery;
use Nnjeim\World\Actions\City\Transformers\IndexTransformer;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $module = 'cities';

	protected string $cacheTag = 'cities';

	protected string $attribute = 'city';

	protected array $defaultFields = [
		'id',
		'name',
	];

	protected array $availableFields = [
		'id',
		'name',
		'state_id',
		'country_id',
		'country_code',
	];

	protected array $availableRelations = [
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

		$this->validateArguments($fields, $filters);

		// cache
		$cities = Cache::rememberForever(
			$this->cacheKey,
			fn () => $this->transform(
				(new IndexQuery($this->validatedFilters, $this->validatedRelations))(),
				array_merge($this->validatedFields, $this->validatedRelations)
			)
		);
		// response
		return $this->formResponse($cities);
	}
}
