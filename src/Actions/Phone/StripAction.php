<?php

namespace Nnjeim\World\Actions\Phone;

use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;

class StripAction extends BaseAction implements ActionInterface
{
	/**
	 * @param  array  $args
	 * @return $this
	 */
	public function execute(array $args): self
	{
		list(
			'number' => $number,
			'phone_code' => $phone_code
			) = $args + [
				'phone_code' => null,
			];

		list(
			'number' => $number,
			'digits' => $digits
			) = stripPhone($number, $phone_code);

		// response
		return $this->respond([
			'number' => $number,
			'digits' => $digits,
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
