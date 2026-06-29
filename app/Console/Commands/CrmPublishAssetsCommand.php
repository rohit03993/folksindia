<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CrmPublishAssetsCommand extends Command
{
    protected $signature = 'crm:publish-assets';

    protected $description = 'Publish Livewire and Filament static assets for CloudPanel/nginx';

    public function handle(): int
    {
        $this->components->info('Publishing Livewire assets (required on CloudPanel — nginx blocks dynamic /livewire-*/ routes)...');

        $this->call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);

        $this->components->info('Publishing Filament assets...');

        $this->call('filament:assets');

        if (! file_exists(public_path('vendor/livewire/manifest.json'))) {
            $this->components->error('Livewire assets missing after publish.');

            return self::FAILURE;
        }

        if (! file_exists(public_path('build/manifest.json'))) {
            $this->components->warn('public/build/manifest.json is missing.');
            $this->line('The public website and student portal need: npm ci && npm run build');
            $this->line('Without it, pages may load unstyled (or 500 on older code). Filament /admin still works.');
        }

        $this->components->success('Static assets published to public/vendor/livewire and public/js/filament.');

        return self::SUCCESS;
    }
}
