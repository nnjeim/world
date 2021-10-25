<?php

namespace Nnjeim\World\Actions\Timezone\Queries;

use Nnjeim\World\Models\Timezone;

class IndexQuery
{
	private array $wheres;
	private array $with;

	public function __construct(array $wheres, array $with)
	{
		$this->wheres = $wheres;
		$this->with = $with;
	}

	public function __invoke()
	{
		/*-- query --*/
		$query = Timezone::query();

		$query->when(
			!empty($this->with),
			fn($q) => $q->with($this->with)
		);

		$query->when(
			!empty($this->wheres),
			fn($q) => $q->where($this->wheres)
		);

		return $query->get();
	}
}
