<?php

namespace Nnjeim\World\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class UpdateGeoipDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:geoip
                            {--license= : MaxMind license key (or set MAXMIND_LICENSE_KEY env variable)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download or update the MaxMind GeoLite2-City database';

    /**
     * MaxMind download URL pattern.
     */
    protected string $downloadUrl = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $licenseKey = $this->option('license') ?? config('world.geolocate.maxmind_license_key');

        if (empty($licenseKey)) {
            $this->error('MaxMind license key is required.');
            $this->line('');
            $this->line('You can provide it in one of these ways:');
            $this->line('  1. Set the MAXMIND_LICENSE_KEY environment variable');
            $this->line('  2. Use the --license option: php artisan world:geoip --license=YOUR_KEY');
            $this->line('  3. Set it in config/world.php under geolocate.maxmind_license_key');
            $this->line('');
            $this->line('Get a free license key at: https://www.maxmind.com/en/geolite2/signup');

            return Command::FAILURE;
        }

        $databasePath = config('world.geolocate.database_path', storage_path('app/geoip/GeoLite2-City.mmdb'));
        $directory = dirname($databasePath);

        // Create directory if it doesn't exist
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info("Created directory: {$directory}");
        }

        $this->info('Downloading GeoLite2-City database from MaxMind...');

        $url = sprintf($this->downloadUrl, $licenseKey);

        try {
            $response = Http::timeout(300)->get($url);

            if (!$response->successful()) {
                if ($response->status() === 401) {
                    $this->error('Invalid license key. Please check your MaxMind license key.');
                } else {
                    $this->error("Failed to download database. HTTP Status: {$response->status()}");
                }
                return Command::FAILURE;
            }

            // Save the tar.gz file temporarily
            $tempFile = $directory . '/GeoLite2-City.tar.gz';
            File::put($tempFile, $response->body());

            $this->info('Extracting database...');

            // Extract the tar.gz file
            $extracted = $this->extractDatabase($tempFile, $directory, $databasePath);

            // Clean up temp file
            File::delete($tempFile);

            if (!$extracted) {
                $this->error('Failed to extract the database file.');
                return Command::FAILURE;
            }

            $this->info("GeoLite2-City database updated successfully!");
            $this->info("Database location: {$databasePath}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error downloading database: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Extract the database from tar.gz archive.
     *
     * @param string $tarFile
     * @param string $directory
     * @param string $databasePath
     * @return bool
     */
    protected function extractDatabase(string $tarFile, string $directory, string $databasePath): bool
    {
        try {
            // Use PharData to extract tar.gz
            $phar = new \PharData($tarFile);

            // Extract to a temp directory
            $tempExtractDir = $directory . '/temp_extract';
            if (File::isDirectory($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
            File::makeDirectory($tempExtractDir, 0755, true);

            $phar->extractTo($tempExtractDir);

            // Find the .mmdb file in the extracted contents
            $files = File::allFiles($tempExtractDir);
            $mmdbFile = null;

            foreach ($files as $file) {
                if ($file->getExtension() === 'mmdb') {
                    $mmdbFile = $file->getPathname();
                    break;
                }
            }

            if ($mmdbFile && File::exists($mmdbFile)) {
                // Move the mmdb file to the final location
                File::move($mmdbFile, $databasePath);

                // Clean up temp extraction directory
                File::deleteDirectory($tempExtractDir);

                return true;
            }

            // Clean up
            File::deleteDirectory($tempExtractDir);

            return false;

        } catch (\Exception $e) {
            $this->error("Extraction error: {$e->getMessage()}");

            // Try using shell commands as fallback
            return $this->extractWithShell($tarFile, $directory, $databasePath);
        }
    }

    /**
     * Fallback extraction using shell commands.
     *
     * @param string $tarFile
     * @param string $directory
     * @param string $databasePath
     * @return bool
     */
    protected function extractWithShell(string $tarFile, string $directory, string $databasePath): bool
    {
        $this->info('Attempting extraction with shell commands...');

        // Check if tar command is available
        exec('which tar', $output, $returnCode);
        if ($returnCode !== 0) {
            $this->error('tar command not found. Please install tar or extract the database manually.');
            return false;
        }

        // Extract tar.gz
        $command = sprintf(
            'cd %s && tar -xzf %s --wildcards "*.mmdb" --strip-components=1 2>/dev/null',
            escapeshellarg($directory),
            escapeshellarg($tarFile)
        );

        exec($command, $output, $returnCode);

        // Look for the extracted mmdb file
        $extractedMmdb = $directory . '/GeoLite2-City.mmdb';
        if (File::exists($extractedMmdb)) {
            if ($extractedMmdb !== $databasePath) {
                File::move($extractedMmdb, $databasePath);
            }
            return true;
        }

        // Search for mmdb file in directory
        $files = File::glob($directory . '/**/GeoLite2-City.mmdb');
        if (!empty($files)) {
            File::move($files[0], $databasePath);
            return true;
        }

        return false;
    }
}
