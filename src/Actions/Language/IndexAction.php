<?php

namespace Nnjeim\World\Actions\Language;

use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\Language\Queries\IndexQuery;
use Nnjeim\World\Actions\Language\Transformers\IndexTransformer;

class IndexAction extends BaseAction implements ActionInterface
{
	use IndexTransformer;

	protected string $cacheTag = 'languages';

	protected string $attribute = 'language';

	protected array $availableFields = [
		'id',
		'code',
		'name',
		'name_native',
		'dir',
	];

	protected array $fields = [
		'id',
		'code',
		'name',
	];

	protected array $relations = [

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
		$currencies = Cache::rememberForever(
			$this->cacheKey,
			fn () => $this->transform((new IndexQuery($this->wheres, $this->with))(), $this->fields)
		);
		// response
		return $this->formResponse($currencies);
	}
}
