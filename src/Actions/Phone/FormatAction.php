<?php

namespace Nnjeim\World\Actions\Phone;

use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;

class FormatAction extends BaseAction implements ActionInterface
{
	/**
	 * @param  array  $args
	 * @return $this
	 */
	public function execute(array $args): self
	{
		list(
			'number' => $number,
			'phone_code' => $phone_code) = $args + [
				'phone_code' => null,
			];

		// response
		return $this->respond([
			'number' => formatPhone($number, $phone_code),
		]);
	}

	/**
	 * @param $data
	 * @return $this
	 */
	private function respond($data): self
	{
		$this->success = true;
		$this->message = trans('world::response.phone.singular');
		$this->data = $data;

		return $this;
	}
}
