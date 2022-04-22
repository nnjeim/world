<?php

namespace Nnjeim\World\Actions\State;

use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\State\Queries\IndexQuery;
use Nnjeim\World\Actions\State\Transformers\IndexTransformer;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $module = 'states';

	protected string $cacheTag = 'states';

	protected string $attribute = 'state';

	protected array $defaultFields = [
		'id',
		'name',
	];

	protected array $availableFields = [
		'id',
		'name',
		'country_id',
		'country_code',
	];

	protected array $availableRelations = [
		'country',
		'cities',
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
