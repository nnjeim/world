<?php

namespace Nnjeim\World\Commands;

class InstallWorldData extends WorldDataCommand
{
    protected $signature = 'world:install {--dry-run : Preview without publishing resources or updating data} {--force : Run in production without confirmation}';

    protected $description = 'Install or update World data while retaining existing IDs and configuration';

    protected bool $publish = true;
}
