<?php

namespace Nnjeim\World\Http\Response;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ResponseBuilder
{
	public const HTTP_OK = 200;
	public const HTTP_CREATED = 201;
	public const HTTP_ACCEPTED = 202;
	public const HTTP_NOT_FOUND = 404;
	public const HTTP_UNPROCESSABLE_ENTITY = 422;

	private array $props = [
		'success' => false,
		'message' => '',
		'data' => [],
		'errors' => [],
		'meta' => [],
		'statusCode' => self::HTTP_OK,
	];

	/**
	 * @param  string  $name
	 * @param $value
	 * @return void
	 */
	public function __set(string $name, $value): void
	{
		$this->props[$name] = $value;
	}

	/**
	 * @param  string  $name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		return $this->props[$name] ?? null;
	}

	/**
	 * @return static
	 */
	public static function make(): static
	{
		return new static();
	}

	/**
	 * @param  bool  $success
	 * @return $this
	 */
	public function setSuccess(bool $success): ResponseBuilder
	{
		$this->success = $success;
		return $this;
	}

	/**
	 * @param  string  $message
	 * @return $this
	 */
	public function setMessage(string $message): ResponseBuilder
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @param  $data
	 * @return $this
	 */
	public function setData($data): ResponseBuilder
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @param  array| null  $errors
	 * @return $this
	 */
	public function setErrors(?array $errors = null): ResponseBuilder
	{
		$this->errors = $errors ?? ($this->success ? [] : ['message' => [$this->message]]);
		return $this;
	}

	/**
	 * @param  array  $meta
	 * @return $this
	 */
	public function setMeta(array $meta): ResponseBuilder
	{
		$this->meta = $meta;
		return $this;
	}

	/**
	 * @param  int  $statusCode
	 * @return $this
	 */
	public function setStatusCode(int $statusCode): ResponseBuilder
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setStatusOk(): ResponseBuilder
	{
		return $this->setStatusCode(
			$this->success
				? self::HTTP_OK
				: self::HTTP_UNPROCESSABLE_ENTITY
		);
	}

	/**
	 * @return $this
	 */
	public function setStatusCreated(): ResponseBuilder
	{
		return $this->setStatusCode(
			$this->success
				? self::HTTP_CREATED
				: self::HTTP_UNPROCESSABLE_ENTITY
		);
	}

	/**
	 * @return $this
	 */
	public function setStatusAccepted(): ResponseBuilder
	{
		return $this->setStatusCode(
			$this->success
				? self::HTTP_ACCEPTED
				: self::HTTP_UNPROCESSABLE_ENTITY
		);
	}

	/**
	 * @param  string  $attribute
	 * @param  bool  $plural
	 * @return $this
	 */
	public function setAttributeMessage(
		string $attribute,
		bool $plural = false
	): ResponseBuilder {
		return $this->setMessage(trans_choice("world::response.attributes.$attribute", (int) $plural + 1));
	}

	/**
	 * @return array
	 */
	private function getPlatformMeta(): array
	{
		return [
			'response_time' => 1000 * number_format((microtime(true) - LARAVEL_START), 2) . ' ms',
		];
	}

	/**
	 * @return Collection
	 */
	public function formPayload(): Collection
	{
		return collect([
			'success' => $this->success,
			'message' => $this->message,
			'data' => $this->data,
		])
			// merge errors
			->when(!$this->success, fn($response) => $response->merge(['errors' => $this->errors]))
			// merge meta
			->merge($this->meta)
			// merge platform meta
			->merge($this->getPlatformMeta());
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_merge_recursive([], $this->props);
	}

	/**
	 * @return JsonResponse
	 */
	public function toJson(): JsonResponse
	{
		return response()->json($this->formPayload(), $this->statusCode);
	}
}
