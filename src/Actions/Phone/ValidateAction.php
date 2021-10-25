<?php

namespace Nnjeim\World\Actions\Phone;

use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\ActionInterface;

class ValidateAction extends BaseAction implements ActionInterface
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
		return $this->formResponse($number, $digits);
	}

	/**
	 * @param $number
	 * @param $digits
	 * @return $this
	 */
	private function formResponse($number, $digits): self
	{
		$this->success = (strlen($number) - $digits) >= 0;
		$this->message = trans('world::response.actions.' . ($this->success ? 'format_valid' : 'format_error'));
		$this->errors = $this->success ? [] : [$this->message];
		$this->statusCode = $this->success ? 202 : 422;

		return $this;
	}
}
