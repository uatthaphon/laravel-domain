<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /** @test */
    public function the_install_command_copies_the_configuration()
    {
        // make sure we're starting from a clean state
        if (File::exists(config_path('laravel-domain.php'))) {
            unlink(config_path('laravel-domain.php'));
        }

        $this->assertFalse(File::exists(config_path('laravel-domain.php')));

        Artisan::call('laravel-domain:install');

        $this->assertTrue(File::exists(config_path('laravel-domain.php')));
    }
}
