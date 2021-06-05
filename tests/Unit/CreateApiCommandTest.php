<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Uatthaphon\LaravelDomain\Console\CreateApiCommand;
use Uatthaphon\LaravelDomain\Console\CreateBaseCommand;
use Tests\TestCase;

class CreateApiCommandTest extends TestCase
{
    use RefreshDatabase;
    private CreateBaseCommand $createBaseCommand;
    private CreateApiCommand $createApiCommand;

    private $mockDomainInput = ['input' => [
        'base_domain' => 'MockDomain',
        'sub_domain' => 'MockSub',
    ]];

    private $mockInput = ['input' => [
        'controller_name' => 'Mock',
        'model_namespace' => 'App/Domain/MockDomain/Models/MockSub',
        'service_namespace' => 'App/Domain/MockDomain/Services/MockSubService',
    ]];

    public function setUp(): void
    {
        parent::setUp();
        $this->createBaseCommand = new CreateBaseCommand;
        $this->prepareModelForTest();
        $this->prepareServiceForTest();

        $this->createApiCommand = new CreateApiCommand;
        $this->createApiCommand->prepareChoices();
    }

    private function cleanUpAllFiles(): void
    {
        $files = array_merge(
            $this->createBaseCommand->createdFiles,
            $this->createApiCommand->createdFiles
        );
        foreach ($files as $file) {
            $apiRoutePath = base_path('routes/api.php');

            if ($file === $apiRoutePath) {
                $content = '<?php' . PHP_EOL . PHP_EOL . 'use Illuminate\Support\Facades\Route;' . PHP_EOL;
                File::put($apiRoutePath, $content);
                continue;
            }

            if (File::exists($file)) {
                unlink($file);
            }
        }
    }

    private function prepareModelForTest()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'callMakeModel',
            $this->mockDomainInput
        );
        $path = $this->getStrictAccessProperty(
            $this->createBaseCommand,
            'createdFiles'
        )['model'];
        $fileContent = file_get_contents($path);

        if (! strpos($fileContent, 'protected $fillable')) {
            $search = '}';
            $replace = '    protected $fillable = [\'name\',\'active\'];' . PHP_EOL . '}';
            $fileContent = str_replace($search, $replace, $fileContent);

            file_put_contents($path, $fileContent);
        }
    }

    private function prepareServiceForTest()
    {
        $this->invokeMethod(
            $this->createBaseCommand,
            'createdServiceFile',
            $this->mockDomainInput
        );
    }

    public function test_create_api_controller()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createApiController',
            $this->mockInput
        );

        $this->assertArrayHasKey('controller', $this->createApiCommand->createdFiles);
    }

    public function test_create_api_controller_test()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createApiControllerTest',
            $this->mockInput
        );

        $this->assertArrayHasKey('controller_test', $this->createApiCommand->createdFiles);
    }

    public function test_create_append_api_route()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createAppendApiRoute',
            $this->mockInput
        );

        $this->assertArrayHasKey('api_route', $this->createApiCommand->createdFiles);
    }

    public function test_create_index_request_api_controller()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createIndexRequestApiController',
            $this->mockInput
        );

        $this->assertArrayHasKey('request_index', $this->createApiCommand->createdFiles);
    }

    public function test_create_store_request_api_controller()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createStoreRequestApiController',
            $this->mockInput
        );

        $this->assertArrayHasKey('request_store', $this->createApiCommand->createdFiles);
    }

    public function test_create_update_request_api_controller()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createUpdateRequestApiController',
            $this->mockInput
        );

        $this->assertArrayHasKey('request_update', $this->createApiCommand->createdFiles);
    }

    public function test_create_resource()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createResource',
            $this->mockInput
        );

        $this->assertArrayHasKey('resource', $this->createApiCommand->createdFiles);
    }

    public function test_create_resource_test()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createResourceTest',
            $this->mockInput
        );

        $this->assertArrayHasKey('resource_test', $this->createApiCommand->createdFiles);
    }

    public function test_create_resource_collection()
    {
        $this->invokeMethod(
            $this->createApiCommand,
            'createResourceCollection',
            $this->mockInput
        );

        $this->assertArrayHasKey('resource_collection', $this->createApiCommand->createdFiles);
    }

    protected function tearDown(): void
    {
        $this->cleanUpAllFiles();
        parent::tearDown();
    }
}
