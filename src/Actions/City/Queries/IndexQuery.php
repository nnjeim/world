<?php

namespace Nnjeim\World\Actions\City\Queries;

use Illuminate\Database\Eloquent\Collection;
use Nnjeim\World\Models\City;

class IndexQuery
{
	private array $wheres;

	private array $with;

	public function __construct(array $wheres, array $with)
	{
		$this->wheres = $wheres;
		$this->with = $with;
	}

	public function __invoke(): Collection
	{
		// query
		$query = City::query();

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
