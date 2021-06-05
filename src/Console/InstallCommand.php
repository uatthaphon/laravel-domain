<?php

namespace Uatthaphon\LaravelDomain\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-domain:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Command help prepare and publish necessary configuration.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-domain-config', '--force' => true]);
    }
}
