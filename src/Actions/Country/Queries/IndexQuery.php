<?php

namespace Nnjeim\World\Actions\Country\Queries;

use Illuminate\Database\Eloquent\Collection;
use Nnjeim\World\Models\Country;

class IndexQuery
{
	private array $wheres;

	private array $with;

	public function __construct(array $wheres, array $with)
	{
		$this->wheres = $wheres;
		$this->with = $with;
	}

	/**
	 * @return Collection
	 */
	public function __invoke(): Collection
	{
		// query
		$query = Country::query();

		$query->when(
			! empty($this->with),
			fn ($q) => $q->with($this->with)
		);

		$query->when(
			! empty($this->wheres),
			fn ($q) => $q->where($this->wheres)
		);

		return $query->get();
	}
}
