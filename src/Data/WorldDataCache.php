<?php

namespace Nnjeim\World\Data;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WorldDataCache
{
    private static function key(): string
    {
        return 'world:data-version:' . hash('sha256', json_encode([
            config('world.connection'), config('world.models'), config('world.migrations'),
        ]));
    }

    public static function prefix(): string
    {
        return self::key() . ':' . Cache::get(self::key(), 'initial') . ':';
    }

    public static function invalidate(): void
    {
        Cache::forever(self::key(), (string) Str::uuid());
    }
}
