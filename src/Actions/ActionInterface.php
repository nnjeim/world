<?php

namespace Nnjeim\World\Actions;

interface ActionInterface
{
	public function execute(array $args, bool $isCacheEnabled): self;
}
