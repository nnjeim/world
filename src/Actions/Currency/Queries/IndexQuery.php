<?php

namespace Nnjeim\World\Actions\Currency\Queries;

use Nnjeim\World\Models\Currency;

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
		$query = Currency::query();

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
