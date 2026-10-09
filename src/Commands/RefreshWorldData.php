<?php

namespace Nnjeim\World\Commands;

class RefreshWorldData extends WorldDataCommand
{
    protected $signature = 'world:refresh {--dry-run : Preview data updates} {--force : Run in production without confirmation}';

    protected $description = 'Update World data without dropping tables or deleting application records';
}
