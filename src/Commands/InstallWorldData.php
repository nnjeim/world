<?php
    
namespace Nnjeim\World\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallWorldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:install';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the world data';
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing nnjeim/world...');
        
        // publish migrations
        Artisan::call('vendor:publish --tag=world');
        // migrate new tables
        Artisan::call('migrate');
        // re-seed the world data
        Artisan::call('db:seed --class=WorldSeeder --database=' . config('world.connection'), array(), $this->getOutput());
    }
}
