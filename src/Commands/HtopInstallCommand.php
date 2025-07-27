<?php

namespace Htop\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class HtopInstallCommand extends Command
{
    protected $signature = 'htop:install';

    protected $description = 'Prepare the HTop system (config, SQLite DB, migrations)';

    public function handle()
    {
        $dbPath = config('database.connections.htop_sqlite.database');

        // 1. Ensure SQLite DB file exists
        if (! File::exists($dbPath)) {
            File::ensureDirectoryExists(dirname($dbPath));
            File::put($dbPath, '');
            $this->info("Created SQLite DB at: $dbPath");
        } else {
            $this->info("SQLite DB already exists at: $dbPath");
        }

        // 2. Run migrations on the htop connection
        Artisan::call('migrate', [
            '--path' => 'vendor/khaledalam/htop/database/migrations',
            '--database' => config('htop.connection'),
        ]);

        $configPath = config_path('reverb.php');

        if (! file_exists($configPath)) {
            if ($this->confirm('Reverb is not configured. Publish its config file now?', true)) {
                $this->call('vendor:publish', ['--tag' => 'reverb-config']);
            }
        }

        if (! file_exists($configPath)) {
            $this->warn('Reverb is not yet configured. Please run:');
            $this->line('php artisan vendor:publish --tag=reverb-config');
            $this->line('Then, set BROADCAST_DRIVER=reverb in your .env file.');
        }

        $this->line(Artisan::output());

        $this->info('HTop installation complete.');
    }
}
