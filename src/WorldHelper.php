<?php

namespace Nnjeim\World;

use Exception;

/**
 * @method static \Nnjeim\World\Actions\BaseAction countries(array $args)
 * @method static \Nnjeim\World\Actions\BaseAction states(array $args)
 * @method static \Nnjeim\World\Actions\BaseAction cities(array $args)
 * @method static \Nnjeim\World\Actions\BaseAction timezones(array $args)
 * @method static \Nnjeim\World\Actions\BaseAction currencies(array $args)
 * @method static \Nnjeim\World\Actions\BaseAction languages(array $args)
 * @method static \Nnjeim\World\Actions\BaseAction geolocate(array $args)
 */
class WorldHelper
{
    private bool $isCacheEnabled;
	private array $availableActions = [
		'countries' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\Country',
			'action' => 'index',
		],
		'states' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\State',
			'action' => 'index',
		],
		'cities' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\City',
			'action' => 'index',
		],
		'timezones' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\Timezone',
			'action' => 'index',
		],
		'currencies' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\Currency',
			'action' => 'index',
		],
		'languages' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\Language',
			'action' => 'index',
		],
		'geolocate' => [
			'actionBasePath' => 'Nnjeim\\World\\Actions\\Geolocate',
			'action' => 'index',
		],
	];

    public function __construct() {
        $this->isCacheEnabled = config('world.cache.enabled', true);
    }

    /**
	 * @param $function
	 * @param  array  $args
	 * @return mixed
	 * @throws Exception
	 */
	public function __call($function, array $args = [])
	{
		list($actionBasePath, $action) = $this->fetchAction($function);

        $isCacheEnabled = $this->isCacheEnabled;
        $this->isCacheEnabled = config('world.cache.defaults.enabled', true); // Reset to default

		return app($this->formActionClass($actionBasePath, $action))->execute(! empty($args) ? $args[0] : [], $isCacheEnabled);
	}

	/**
	 * @param  string  $function
	 * @return array
	 * @throws Exception
	 */
	private function fetchAction(string $function): array
	{
		if (! in_array(strtolower($function), array_keys($this->availableActions))) {
			throw new Exception('Method not found!');
		}

		['actionBasePath' => $actionBasePath, 'action' => $action] = $this->availableActions[strtolower($function)];

		return [$actionBasePath, $action];
	}

	/**
	 * @param  string  $actionBasePath
	 * @param  string  $function
	 * @return string
	 */
	private function formActionClass(string $actionBasePath, string $function): string
	{
		return implode('\\', [$actionBasePath, ucfirst($function)]) . 'Action';
	}

	/**
	 * @param string $requestLocale
	 * @return $this
	 */
	public function setLocale(string $requestLocale): self
	{
		$setLocale = in_array($requestLocale, config('world.accepted_locales'), true)
			? $requestLocale
			: config('app.fallback_locale');

        session()->put('nnjeim-world-locale', $setLocale);

		return $this;
	}

    public function withCaching(): self
    {
        $this->isCacheEnabled = true;
        return $this;
    }

    public function withoutCaching(): self
    {
        $this->isCacheEnabled = false;
        return $this;
    }
}
