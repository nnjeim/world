<?php

namespace Nnjeim\World\Actions\State\Queries;

use Illuminate\Database\Eloquent\Collection;
use Nnjeim\World\Models\State;

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
		$query = State::query();

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
