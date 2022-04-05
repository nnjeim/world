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

	protected array $defaultFields = [
		'id',
		'code',
		'name',
	];

	protected array $availableFields = [
		'id',
		'code',
		'name',
		'name_native',
		'dir',
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
