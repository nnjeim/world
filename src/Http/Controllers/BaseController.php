<?php

namespace Nnjeim\World\Http\Controllers;

use Nnjeim\World\Http\Controllers\Traits\ResponseBuilder;

use Illuminate\Http\JsonResponse;

class BaseController
{
	use ResponseBuilder;

    protected string $requestBasePath = 'Nnjeim\\World\\Http\\Requests';

    protected string $actionBasePath = 'Nnjeim\\World\\Actions';

	/**
	 * @param $function
	 * @param  null  $args
	 * @return JsonResponse
	 */
	public function __call($function, $args = null): JsonResponse
	{
		return $this->respond($function);
	}

	/**
	 * @param  string  $function
	 * @return JsonResponse
	 */
	protected function respond(string $function): JsonResponse
	{
		/*
		 * Request class
		 */
		$requestClass = $this->composeRequestClass($function);

		$actionParams = class_exists($requestClass) ? app($requestClass)->validated() : null;

		/*
		 * Action
		 */
		$action = app($this->composeActionClass($function))->execute($actionParams);

		/*
		 * Response
		 */
		return $this->formResponse($action);
	}

	/**
	 * @param  string  $function
	 * @return string
	 */
	private function composeRequestClass(string $function)
	{
		return implode('\\', [$this->requestBasePath, ucfirst($function)]) . 'Request';
	}

	/**
	 * @param  string  $function
	 * @return string
	 */
	private function composeActionClass(string $function)
	{
		return implode('\\', [$this->actionBasePath, ucfirst($function)]) . 'Action';
	}
}
