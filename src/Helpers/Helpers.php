<?php

if (! function_exists('stripPhone')) {
	function stripPhone($number, $phone_code = null): array
	{
		if ($phone_code === null) {
			$phone_code = config('world.default_phone_code');
		}

		$countries = resolve('countries');

		$country = $countries->first(fn ($country) => $country['phone_code'] === $phone_code);

		$dialling_pattern = $country['dialling_pattern'];

		if ($number != null) {
			/* -- strip number from non numeric -- */
			$number = strval(preg_replace('/[^0-9]+/', '', $number));
			/* -- strip number from prefixes -- */
			foreach (['+', '0', '00'] as $str) {
				$number = ltrim($number, $str);
			}
			/* -- add country code -- */
			if (substr($number, 0, intval($dialling_pattern[0])) !== $phone_code) {
				$number = $phone_code . $number;
			}
		}

		$expectedLength = 0;

		for ($i = 0; $i < strlen($dialling_pattern); $i++) {
			$expectedLength += intval($dialling_pattern[$i]);
		}

		return [
			'number' => $number,
			'digits' => $expectedLength,
		];
	}
}

if (! function_exists('formatPhone')) {
	function formatPhone($number, $phone_code = null): string
	{
		if ($phone_code === null) {
			$phone_code = config('world.default_phone_code');
		}

		$countries = resolve('countries');

		$country = $countries->first(fn ($country) => $country['phone_code'] === $phone_code);

		$dialling_pattern = $country['dialling_pattern'];

		$separator = ' ';

		$prefix = '+';

		/* -- strip number from non numeric -- */
		$number = strval(preg_replace('/[^0-9]+/', '', $number));
		/* -- strip number from prefixes -- */
		foreach (['+', '0', '00'] as $str) {
			$number = ltrim($number, $str);
		}
		/* -- add country code -- */
		if (substr($number, 0, intval($dialling_pattern[0])) !== $phone_code) {
			$number = $phone_code . $number;
		}
		/* -- build preg_match expression -- */
		$pregString = '/^';

		$expectedLength = 0;

		for ($i = 0; $i < strlen($dialling_pattern); $i++) {
			$expectedLength += intval($dialling_pattern[$i]);

			$pregString .= '(\d{' . $dialling_pattern[$i] . '})';
		}

		$pregString .= '$/';
		/* -- strip number to expected length -- */
		$strippedNumber = substr($number, 0, $expectedLength);

		$extraLength = strlen($number) - $expectedLength;

		$extraNumbers = $extraLength > 0 ? substr($number, -$extraLength) : '';
		/* -- format stripped number -- */
		$match = preg_match($pregString, $strippedNumber, $matches);

		$formattedNumber = '';
		/* -- compose formatted number -- */
		if ($match) {
			for ($j = 1; $j < count($matches); $j++) {
				$formattedNumber .= ($matches[$j]) . $separator;
			}

			$formattedNumber = $prefix . substr($formattedNumber, 0, -1);
		}
		/* -- add extra numbers -- */
		return $formattedNumber . $extraNumbers;
	}
}
