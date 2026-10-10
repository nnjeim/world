<?php

namespace Nnjeim\World\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Data\WorldDataset;
use Nnjeim\World\Data\WorldDataSynchronizer;
use Throwable;

abstract class WorldDataCommand extends Command
{
    use ConfirmableTrait;

    protected bool $publish = false;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if (! $dryRun && ! $this->confirmToProceed()) {
            return self::FAILURE;
        }
        $lock = Cache::lock('world:data-command:' . hash('sha256', json_encode([
            config('world.connection'), config('world.migrations'),
        ])), 3600);
        if (! $lock->get()) {
            $this->error('Another World data update is running. Retry after it finishes.');
            return self::FAILURE;
        }
        try {
            // Validate every source record before publishing resources or changing data.
            $dataset = app(WorldDataset::class);
            if (! $dryRun) {
                if ($this->publish && Artisan::call('vendor:publish', ['--tag' => 'world']) !== self::SUCCESS) {
                    throw new \RuntimeException('World resource publishing failed.');
                }
                $status = Artisan::call('migrate', [
                    '--database' => config('world.connection'), '--path' => realpath(__DIR__ . '/../Database/Migrations'),
                    '--realpath' => true, '--force' => true,
                ], $this->output);
                if ($status !== self::SUCCESS) {
                    throw new \RuntimeException('World migrations failed; data has not been updated.');
                }
            }
            $summary = app(WorldDataSynchronizer::class)->sync($dataset, $dryRun);
            $this->table(['Module', 'Created', 'Updated', 'Unchanged', 'Local fields preserved'], array_map(
                fn ($module, $counts) => array_merge([$module], array_values($counts)), array_keys($summary), $summary
            ));
            $this->info($dryRun ? 'Preview complete. No files or data were changed.' : 'World data updated. Existing IDs and application rows were retained.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
