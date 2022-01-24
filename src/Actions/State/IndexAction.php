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

	protected string $cacheTag = 'states';

	protected string $attribute = 'state';

	protected array $availableFields = [
		'id',
		'name',
		'country_id',
		'country',
		'cities',
	];

	protected array $fields = [
		'id',
		'name',
	];

	protected array $relations = [
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

		$this->formFields($fields);
		$this->formFilters($filters);
		$this->formWith();
		$this->formCacheKey();

		// cache
		$cities = Cache::rememberForever(
			$this->cacheKey,
			fn () => $this->transform((new IndexQuery($this->wheres, $this->with))(), $this->fields)
		);
		// response
		return $this->formResponse($cities);
	}
}
