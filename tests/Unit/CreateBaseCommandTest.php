<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Uatthaphon\LaravelDomain\Console\CreateBaseCommand;
use Tests\TestCase;

class CreateBaseCommandTest extends TestCase
{
    private CreateBaseCommand $createBaseCommand;

    private $mockDomainInput = ['input' => [
        'base_domain' => 'Base',
        'sub_domain' => 'Sub',
    ]];

    public function setUp(): void
    {
        parent::setUp();
        $this->createBaseCommand = new CreateBaseCommand;
    }

    private function cleanUpAllFiles(): void
    {
        foreach ($this->createBaseCommand->createdFiles as $key => $file) {
            $file = $key === 'migration' ? database_path('migrations/' . $file) : $file;

            Log::info($file);
            if (File::exists($file)) {
                unlink($file);
            }
        }
    }

    public function test_call_make_migration_with_corrected_path()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'callMakeMigration',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('migration', $this->createBaseCommand->createdFiles);
    }

    public function test_call_make_factory_with_corrected_path()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'callMakeFactory',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('factory', $this->createBaseCommand->createdFiles);
    }

    public function test_call_make_seeder_with_corrected_path()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'callMakeSeeder',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('seeder', $this->createBaseCommand->createdFiles);
    }

    public function test_call_make_model_with_corrected_path()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'callMakeModel',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('model', $this->createBaseCommand->createdFiles);
    }

    public function test_created_repository_successfully()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'createRepositoryFile',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('repository', $this->createBaseCommand->createdFiles);
    }

    public function test_created_service_successfully()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'createdServiceFile',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('service', $this->createBaseCommand->createdFiles);
    }

    public function test_create_service_test_file_correctly()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'createServiceTestFile',
            $this->mockDomainInput
        );

        $this->assertArrayHasKey('service_test', $this->createBaseCommand->createdFiles);
    }

    protected function tearDown(): void
    {
        $this->cleanUpAllFiles();
        parent::tearDown();
    }
}
