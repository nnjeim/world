<?php

namespace Nnjeim\World\Actions\Currency\Queries;

use Illuminate\Database\Eloquent\Collection;

class IndexQuery
{
	private array $wheres;

	private array $with;

	private ?string $search;

	public function __construct(array $wheres, array $with, ?string $search = null)
	{
		$this->wheres = $wheres;
		$this->with = $with;
		$this->search = $search;
	}

	public function __invoke(): Collection
	{
		// query
		$currencyModel = config('world.models.currencies');
		$query = $currencyModel::query();

		$query->when(
			! empty($this->with),
			fn ($q) => $q->with($this->with)
		);

		$query->when(
			! empty($this->wheres),
			fn ($q) => $q->where($this->wheres)
		);

		$query->when(
			$this->search !== null,
			fn ($q) => $q
				->where('name', 'like', '%' . $this->search . '%')
				->orWhere('code', 'like', '%' . $this->search . '%')
		);

		return $query->get();
	}
}
