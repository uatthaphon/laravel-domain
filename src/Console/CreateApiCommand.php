<?php

namespace Uatthaphon\LaravelDomain\Console;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class CreateApiCommand extends BaseCommand
{
    protected $signature = 'laravel-domain:api';

    protected $description = 'Create Api Domain Driven Design structure.';

    protected array $services;
    protected array $models;

    public function __construct()
    {
        parent::__construct();
        $this->prepareChoices();
    }

    public function prepareChoices()
    {
        $this->services = $this->getExistingDomainServices();
        $this->models = $this->getExistingDomainModels();
    }

    public function handle()
    {
        $this->info('Running API Domain Driven Design structure Generator...');

        $output = '';

        $input['controller_name'] = $this->ask('Please Name your Api Controller Name');

        if (empty($input['controller_name'])) {
            $this->comment('You not given any Api Controller Name, We assume that you are not interest in create new api controller. then see you next time.');
            return;
        }

        $input['service_namespace'] = $name = $this->choice(
            'Please choose your service that you need to use in the controller?',
            Arr::pluck($this->services, 'namespace'),
            0
        );

        $input['model_namespace'] = $name = $this->choice(
            'Please choose your model that you need to use in the controller?',
            Arr::pluck($this->models, 'namespace'),
            0
        );

        $input['controller_name'] = str_replace(' ', '', $input['controller_name']);
        $input = $this->capitalDomainValues($input);

        $output = $this->createAppendApiRoute($input);
        $this->apiOutputLine($output);

        $output = $this->createApiController($input);
        $this->apiOutputLine($output);

        $output = $this->createApiControllerTest($input);
        $this->apiOutputLine($output);

        $output = $this->createIndexRequestApiController($input);
        $this->apiOutputLine($output);

        $output = $this->createStoreRequestApiController($input);
        $this->apiOutputLine($output);

        $output = $this->createUpdateRequestApiController($input);
        $this->apiOutputLine($output);

        $output = $this->createResource($input);
        $this->apiOutputLine($output);

        $output = $this->createResourceTest($input);
        $this->apiOutputLine($output);

        $output = $this->createResourceCollection($input);
        $this->apiOutputLine($output);
    }

    protected function createAppendApiRoute(array $input): string
    {
        $apiRoutePath = base_path('routes/api.php');

        if (File::exists($apiRoutePath) === false) {
            $content = '<?php' . PHP_EOL . PHP_EOL . 'use Illuminate\Support\Facades\Route;' . PHP_EOL;
            File::put($apiRoutePath, $content);
        }

        $name = $this->getRouteName($input);
        $controller = $this->getNamespace(self::CONTROLLER, $input) . '\\' . $this->getClass(self::CONTROLLER, $input) . '::class';
        $content = PHP_EOL . 'Route::apiResource(\'' . $name . '\', ' . $controller . ', [\'as\' => \'api\'])->parameters([\'' . $name . '\' => \'id\']);';

        if (! File::append($apiRoutePath, $content)) {
            return 'Fail to Update Api route file!!';
        }

        $this->createdFiles['api_route'] = $apiRoutePath;

        return 'Updated Api Route: ' . $this->createdFiles['api_route'];
    }

    protected function createApiController(array $input): string
    {
        $service = $this->getSelectedService($input);
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/controller.api.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::CONTROLLER, $input),
            '{{ namespacedService }}' => str_replace('/', '\\', $service['namespace']),
            '{{ class }}' => $this->getClass(self::CONTROLLER, $input),
            '{{ service }}' => $this->getServiceClass($service),
            '{{ serviceVariable }}' => $this->getServiceVariableName($service),
            '{{ model }}' => $this->getModelClass($model),
            '{{ resource }}' => $this->getClass(self::RESOURCE, $input),
            '{{ resourceCollection }}' => $this->getClass(self::RESOURCE_COLLECTION, $input),
            '{{ fillable }}' => $this->getFillableStr($model),
            '{{ createInput }}' => $this->getFillableInput($model, $isCreateInput = true),
            '{{ UpdateInput }}' => $this->getFillableInput($model, $isCreateInput = false),
        ];
        $type = self::CONTROLLER;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Controller file!!';
        }

        $this->createdFiles['controller'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Controller: ' . $this->createdFiles['controller'];
    }

    protected function createApiControllerTest(array $input): string
    {
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/controllerTest.api.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::CONTROLLER, $input),
            '{{ namespacedModel }}' => str_replace('/', '\\', $model['namespace']),
            '{{ class }}' => $this->getClass(self::CONTROLLER_TEST, $input),
            '{{ model }}' => $this->getModelClass($model),
            '{{ modelVariable }}' => $this->getModelVariable($model, self::CONTROLLER_TEST),
            '{{ route }}' => $this->getRouteName($input),
            '{{ fillable }}' => $this->getFillableStr($model, 20),
            '{{ inputForTest }}' => $this->getFillableTestInput($model),
        ];
        $type = self::CONTROLLER_TEST;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Controller file!!';
        }

        $this->createdFiles['controller_test'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Controller Test: ' . $this->createdFiles['controller_test'];
    }

    protected function createIndexRequestApiController(array $input): string
    {
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/Request.api.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::REQUEST_INDEX, $input),
            '{{ class }}' => $this->getClass(self::REQUEST_INDEX, $input),
            '{{ validateFields }}' => $this->getValidateFields($model),
        ];
        $type = self::REQUEST_INDEX;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Request Index file!!';
        }

        $this->createdFiles['request_index'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Request Index: ' . $this->createdFiles['request_index'];
    }

    protected function createStoreRequestApiController(array $input): string
    {
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/Request.api.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::REQUEST_STORE, $input),
            '{{ class }}' => $this->getClass(self::REQUEST_STORE, $input),
            '{{ validateFields }}' => $this->getValidateFields($model),
        ];
        $type = self::REQUEST_STORE;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Request Store file!!';
        }

        $this->createdFiles['request_store'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Request Store: ' . $this->createdFiles['request_store'];
    }

    protected function createUpdateRequestApiController(array $input): string
    {
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/Request.api.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::REQUEST_UPDATE, $input),
            '{{ class }}' => $this->getClass(self::REQUEST_UPDATE, $input),
            '{{ validateFields }}' => $this->getValidateFields($model),
        ];
        $type = self::REQUEST_UPDATE;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Request Update file!!';
        }

        $this->createdFiles['request_update'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Request Update: ' . $this->createdFiles['request_update'];
    }

    protected function createResource(array $input): string
    {
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/Resource.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::RESOURCE, $input),
            '{{ class }}' => $this->getClass(self::RESOURCE, $input),
            '{{ resourceFields }}' => $this->getResourceFields($model),
        ];
        $type = self::RESOURCE;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Resource file!!';
        }

        $this->createdFiles['resource'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Resource: ' . $this->createdFiles['resource'];
    }

    protected function createResourceTest(array $input): string
    {
        $model = $this->getSelectedModel($input);
        $stubPath = __DIR__ . '/../../stubs/ResourceTest.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::RESOURCE_TEST, $input),
            '{{ namespacedModel }}' => str_replace('/', '\\', $model['namespace']),
            '{{ namespacedResource }}' => $this->getNamespace(self::RESOURCE, $input) . '\\' . $this->getClass(self::RESOURCE, $input),
            '{{ class }}' => $this->getClass(self::RESOURCE_TEST, $input),
            '{{ resourceTestInput }}' => $this->getResourceTestInput($model),
            '{{ model }}' => $this->getModelClass($model),
            '{{ modelVariable }}' => $this->getModelVariable($model, self::RESOURCE_TEST),
            '{{ resource }}' => $this->getClass(self::RESOURCE, $input),
        ];
        $type = self::RESOURCE_TEST;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Resource Test file!!';
        }

        $this->createdFiles['resource_test'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Resource Test: ' . $this->createdFiles['resource_test'];
    }

    protected function createResourceCollection(array $input): string
    {
        $stubPath = __DIR__ . '/../../stubs/ResourceCollection.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::RESOURCE_COLLECTION, $input),
            '{{ class }}' => $this->getClass(self::RESOURCE_COLLECTION, $input),
            '{{ resourceVariable }}' => $this->getResourceVariable($input, self::RESOURCE),
            '{{ resource }}' => $this->getClass(self::RESOURCE, $input),
        ];
        $type = self::RESOURCE_COLLECTION;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Api Resource Collection file!!';
        }

        $this->createdFiles['resource_collection'] = $this->getApiDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Created Api Resource Collection: ' . $this->createdFiles['resource_collection'];
    }

    protected function getSelectedService(array $input)
    {
        return Arr::first($this->services, function ($value) use ($input) {
            return $value['namespace'] === $input['service_namespace'];
        });
    }

    protected function getSelectedModel(array $input)
    {
        return Arr::first($this->models, function ($value) use ($input) {
            return $value['namespace'] === $input['model_namespace'];
        });
    }

    protected function getServiceClass(array $service)
    {
        return $service['filename'];
    }

    protected function getServiceVariableName(array $service)
    {
        return lcfirst($service['filename']);
    }

    protected function getModelClass(array $model)
    {
        return $model['filename'];
    }

    protected function initialModel(array $model): Model
    {
        $modelNamespace = $model['namespace'] ?? $model['model_namespace'];
        $modelNamespace = str_replace('/', '\\', $modelNamespace);

        return new $modelNamespace;
    }

    protected function getFillable(array $model): array
    {
        $model = $this->initialModel($model);

        return $model->getFillable();
    }

    protected function getFillableStr(array $model, $customPadNumber = 12): string
    {
        $fillable = $this->getFillable($model);

        $firstKey = array_key_first($fillable);
        $lastKey = array_key_last($fillable);

        $fillableInputStr = '';

        foreach ($fillable as $key => $field) {
            $str = '\'' . $field . '\',';
            $pad = $key === $firstKey ? '' : str_repeat(' ', $customPadNumber);
            $newLine = $key === $lastKey ? '' : PHP_EOL;

            $fillableInputStr .= $pad . $str . $newLine;
        }

        return $fillableInputStr;
    }

    protected function getFillableInput(array $model, $isCreateInput = true): string
    {
        $fillable = $this->getFillable($model);

        $firstKey = array_key_first($fillable);
        $lastKey = array_key_last($fillable);

        $fillableInputStr = '';

        foreach ($fillable as $key => $field) {
            $padNumber = $isCreateInput ? 16 : 24;
            $str = '\'' . $field . '\' => $params[\'' . $field . '\'],';
            $pad = $key === $firstKey ? '' : str_repeat(' ', $padNumber);
            $newLine = $key === $lastKey ? '' : PHP_EOL;

            $fillableInputStr .= $pad . $str . $newLine;
        }

        return $fillableInputStr;
    }

    protected function getFillableTestInput(array $model): string
    {
        $fillable = $this->getFillable($model);

        $firstKey = array_key_first($fillable);
        $lastKey = array_key_last($fillable);

        $fillableInputStr = '';

        foreach ($fillable as $key => $field) {
            $padNumber = 12;
            $str = '\'' . $field . '\' => $this->faker->name,';
            $pad = $key === $firstKey ? '' : str_repeat(' ', $padNumber);
            $newLine = $key === $lastKey ? '' : PHP_EOL;

            $fillableInputStr .= $pad . $str . $newLine;
        }

        return $fillableInputStr;
    }

    protected function getValidateFields(array $model): string
    {
        $fillable = $this->getFillable($model);

        $firstKey = array_key_first($fillable);
        $lastKey = array_key_last($fillable);

        $validateFieldsStr = '';

        foreach ($fillable as $key => $field) {
            $padNumber = 12;
            $str = '\'' . $field . '\' => [\'nullable\'],';
            $pad = $key === $firstKey ? '' : str_repeat(' ', $padNumber);
            $newLine = $key === $lastKey ? '' : PHP_EOL;

            $validateFieldsStr .= $pad . $str . $newLine;
        }

        return $validateFieldsStr;
    }

    protected function getResourceFields(array $model): string
    {
        $fillable = $this->getFillable($model);

        $firstKey = array_key_first($fillable);
        $lastKey = array_key_last($fillable);

        $resourceFieldsStr = '';

        foreach ($fillable as $key => $field) {
            $padNumber = 12;
            $str = '\'' . $field . '\' => $this->' . $field . ',';
            $pad = $key === $firstKey ? '' : str_repeat(' ', $padNumber);
            $newLine = $key === $lastKey ? '' : PHP_EOL;

            $resourceFieldsStr .= $pad . $str . $newLine;
        }

        return $resourceFieldsStr;
    }

    protected function getResourceTestInput(array $model): string
    {
        $fillable = $this->getFillable($model);
        $modelVariable = $this->getModelVariable($model, self::RESOURCE_TEST);

        $firstKey = array_key_first($fillable);
        $lastKey = array_key_last($fillable);

        $resourceFieldsStr = '';

        foreach ($fillable as $key => $field) {
            $padNumber = 12;
            $str = '\'' . $field . '\' => $' . $modelVariable . '->' . $field . ',';
            $pad = $key === $firstKey ? '' : str_repeat(' ', $padNumber);
            $newLine = $key === $lastKey ? '' : PHP_EOL;

            $resourceFieldsStr .= $pad . $str . $newLine;
        }

        return $resourceFieldsStr;
    }
}
