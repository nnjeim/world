<?php

namespace Nnjeim\World\Actions\Phone;

use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\ActionInterface;

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
			'phone_code' => $phone_code,
			) = $args + [
				'phone_code' => null
			];

		list(
			'number' => $number,
			'digits' => $digits,
			) = stripPhone($number, $phone_code);

		/*-- Response --*/
		return $this->formResponse([
			'number' => $number,
			'digits' => $digits,
		]);
	}

	/**
	 * @param $data
	 * @return $this
	 */
	private function formResponse($data): self
	{
		$this->success = true;
		$this->message = trans('world::response.phone.singular');
		$this->data = $data;

		return $this;
	}
}
