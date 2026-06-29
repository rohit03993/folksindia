<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrmDoctorCommand extends Command
{
    protected $signature = 'crm:doctor';

    protected $description = 'Diagnose common production issues (permissions, assets, autoload, database)';

    public function handle(): int
    {
        $ok = true;

        foreach ([
            storage_path('logs'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('app'),
            base_path('bootstrap/cache'),
        ] as $path) {
            if (! is_writable($path)) {
                $this->components->error("Not writable: {$path}");
                $ok = false;
            } else {
                $this->line("OK writable: {$path}");
            }
        }

        if (! file_exists(base_path('vendor/autoload.php'))) {
            $this->components->error('vendor/ missing — run composer install');
            $ok = false;
        }

        if (blank(config('app.key'))) {
            $this->components->error('APP_KEY is empty — run php artisan key:generate');
            $ok = false;
        }

        if (file_exists(base_path('app/Jobs')) && ! is_dir(base_path('app/Jobs'))) {
            $this->components->error('app/Jobs is a FILE — must be a folder. Pull latest code (RunWhatsAppCampaignJob.php).');
            $ok = false;
        }

        if (! file_exists(public_path('vendor/livewire/manifest.json'))) {
            $this->components->warn('Livewire assets missing — run php artisan crm:publish-assets');
            $ok = false;
        }

        if (! file_exists(public_path('build/manifest.json'))) {
            $this->components->warn('Vite build missing — run npm ci && npm run build (public site styling)');
        }

        if (! file_exists(public_path('storage'))) {
            $this->components->warn('public/storage symlink missing — run php artisan storage:link');
        }

        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $this->line('OK database connection');
        } catch (\Throwable $exception) {
            $this->components->error('Database failed: '.$exception->getMessage());
            $ok = false;
        }

        if (class_exists(\App\Jobs\RunWhatsAppCampaignJob::class)) {
            $this->line('OK App\\Jobs\\RunWhatsAppCampaignJob autoload');
        } else {
            $this->components->error('Cannot autoload App\\Jobs\\RunWhatsAppCampaignJob');
            $ok = false;
        }

        if (! $ok) {
            $this->newLine();
            $this->line('Also check: tail -50 storage/logs/laravel.log');
            $this->line('Run as web user: sudo -u www-data php artisan about');

            return self::FAILURE;
        }

        $this->components->success('Checks passed.');

        return self::SUCCESS;
    }
}
