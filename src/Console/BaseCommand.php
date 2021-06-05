<?php

namespace Uatthaphon\LaravelDomain\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BaseCommand extends Command
{
    // base
    const SERVICES = 'Services';
    const SERVICES_TEST = 'ServicesTest';
    const REPOSITORIES = 'Repositories';
    const MODELS = 'Models';

    // api
    const CONTROLLER = 'Controller';
    const CONTROLLER_TEST = 'ControllerTest';
    const REQUEST_INDEX = 'RequestIndex';
    const REQUEST_STORE = 'RequestStore';
    const REQUEST_UPDATE = 'RequestUpdate';
    const RESOURCE = 'Resource';
    const RESOURCE_TEST = 'ResourceTest';
    const RESOURCE_COLLECTION = 'ResourceCollection';

    protected $baseGroup = [
        self::SERVICES,
        self::SERVICES_TEST,
        self::REPOSITORIES,
        self::MODELS,
    ];

    protected $apiGroup = [
        self::CONTROLLER,
        self::CONTROLLER_TEST,
        self::REQUEST_INDEX,
        self::REQUEST_STORE,
        self::REQUEST_UPDATE,
        self::RESOURCE,
        self::RESOURCE_TEST,
        self::RESOURCE_COLLECTION,
    ];

    protected function outputLine(string $output, string $name = 'Base'): void
    {
        $this->line("<comment>Laravel Domain {$name} output:</comment> {$output}");
    }

    protected function apiOutputLine(string $output): void
    {
        $this->outputLine($output, 'API');
    }

    protected function createStub(string $stubPath, array $input, array $replacements, string $type): bool
    {
        if (File::exists($stubPath) === false) {
            return false;
        }

        $stub = File::get($stubPath);

        $stub = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );

        $fileDirectory = '';

        if (in_array($type, $this->baseGroup)) {
            $fileDirectory = $this->getAppDirectory($type, $input);
        } elseif (in_array($type, $this->apiGroup)) {
            $fileDirectory = $this->getApiDirectory($type, $input);
        }

        $fileName = $this->getFileName($type, $input);

        if (! File::exists($fileDirectory)) {
            File::makeDirectory($fileDirectory, 0755, true);
        }

        File::put($fileDirectory . '/' . $fileName, $stub);

        return true;
    }

    protected function getAppDirectory(string $type, array $input): string
    {
        $directory = app_path('Domain/' . $input['base_domain']);

        switch ($type) {
            case self::REPOSITORIES:
                $directory = $directory . '/Repositories';
                break;
            case self::SERVICES:
            case self::SERVICES_TEST:
                $directory = $directory . '/Services';
                break;
            case self::MODELS:
                $directory = $directory . '/Models';
                break;
            default:
                break;
        }

        return $directory;
    }

    protected function getApiDirectory(string $type, array $input): string
    {
        if (in_array($type, [self::REQUEST_INDEX, self::REQUEST_STORE, self::REQUEST_UPDATE])) {
            return app_path('Http/Requests/Api/' . $input['controller_name'] . $this->getSuffix(self::CONTROLLER));
        }

        if (in_array($type, [self::RESOURCE, self::RESOURCE_TEST, self::RESOURCE_COLLECTION])) {
            return app_path('Http/Resources/Api/' . $input['controller_name']);
        }

        return app_path('Http/Controllers/Api');
    }

    protected function getFileName(string $type, array $input): string
    {
        if (in_array($type, [self::CONTROLLER, self::CONTROLLER_TEST])) {
            return $input['controller_name'] . $this->getSuffix($type) . '.php';
        }

        if ($type === self::REQUEST_INDEX) {
            return 'IndexRequest.php';
        }

        if ($type === self::REQUEST_STORE) {
            return 'StoreRequest.php';
        }

        if ($type === self::REQUEST_UPDATE) {
            return 'UpdateRequest.php';
        }

        if (in_array($type, [self::RESOURCE, self::RESOURCE_TEST, self::RESOURCE_COLLECTION])) {
            return $input['controller_name'] . $this->getSuffix($type) . '.php';
        }

        return $input['sub_domain'] . $this->getSuffix($type) . '.php';
    }

    protected function replaceNewLineToEmpty(string $output): string
    {
        return str_replace("\n", '', $output);
    }

    protected function getSuffix(string $type)
    {
        $suffix = '';

        switch ($type) {
            case self::REPOSITORIES:
                $suffix = 'Repo';
                break;
            case self::SERVICES:
                $suffix = 'Service';
                break;
            case self::SERVICES_TEST:
                $suffix = 'ServiceTest';
                break;
            case self::CONTROLLER:
                $suffix = 'Controller';
                break;
            case self::CONTROLLER_TEST:
                $suffix = 'ControllerTest';
                break;
            case self::RESOURCE:
                $suffix = 'Resource';
                break;
            case self::RESOURCE_TEST:
                $suffix = 'ResourceTest';
                break;
            case self::RESOURCE_COLLECTION:
                $suffix = 'ResourceCollection';
                break;
            default:
                break;
        }

        return $suffix;
    }

    protected function getNamespace(string $type, array $input): string
    {
        if (in_array($type, [self::CONTROLLER, self::CONTROLLER_TEST])) {
            return 'App\\Http\\Controllers\\Api';
        }

        if (in_array($type, [self::REQUEST_INDEX, self::REQUEST_STORE, self::REQUEST_UPDATE])) {
            return 'App\\Http\\Requests\\Api\\' . $input['controller_name'] . $this->getSuffix(self::CONTROLLER);
        }

        if (in_array($type, [self::RESOURCE, self::RESOURCE_TEST, self::RESOURCE_COLLECTION])) {
            return 'App\\Http\\Resources\\Api\\' . $input['controller_name'];
        }

        return 'App\\Domain\\' . $input['base_domain'] . '\\' . $type;
    }

    protected function getNamespaceModel(array $input): string
    {
        return $this->getNamespace(self::MODELS, $input) . '\\' . $input['sub_domain'];
    }

    protected function getNamespacedRepository(array $input): string
    {
        return $this->getNamespace(self::REPOSITORIES, $input) . '\\' . $this->getClass(self::REPOSITORIES, $input);
    }

    protected function getClass(string $type, array $input): string
    {
        if (in_array($type, [self::CONTROLLER, self::CONTROLLER_TEST])) {
            return $input['controller_name'] . $this->getSuffix($type);
        }

        if ($type === self::REQUEST_INDEX) {
            return 'IndexRequest';
        }

        if ($type === self::REQUEST_STORE) {
            return 'StoreRequest';
        }

        if ($type === self::REQUEST_UPDATE) {
            return 'UpdateRequest';
        }

        if (in_array($type, [
            self::RESOURCE,
            self::RESOURCE_TEST,
            self::RESOURCE_COLLECTION,
        ])) {
            return $input['controller_name'] . $this->getSuffix($type);
        }

        return $input['sub_domain'] . $this->getSuffix($type);
    }

    protected function getModel(array $input): string
    {
        return $input['sub_domain'];
    }

    protected function getModelVariable(array $input, $type = self::MODELS): string
    {
        if (in_array($type, $this->apiGroup)) {
            return lcfirst($input['filename']);
        }

        return lcfirst($this->getModel($input));
    }

    protected function getResourceVariable(array $input, $type = self::RESOURCE): string
    {
        return lcfirst($input['controller_name']) . $this->getSuffix($type);
    }

    protected function getTable(array $input): string
    {
        return Str::snake(Str::plural($input['sub_domain']));
    }

    protected function getRepositoryVariable($input)
    {
        return lcfirst($this->getClass(self::REPOSITORIES, $input));
    }

    protected function capitalDomainValues(array $input): array
    {
        $result = [];

        foreach ($input as $key => $value) {
            $result[$key] = $this->capitalValue($value);
        }

        return $result;
    }

    protected function capitalValue(string $value): string
    {
        return Str::ucfirst($value);
    }

    protected function getExistingDomainFilesInfo($type = self::SERVICES)
    {
        $filesInfo = [];
        $path = '';

        if ($type === self::SERVICES) {
            $path = app_path('Domain/*/Services');
        } elseif ($type === self::MODELS) {
            $path = app_path('Domain/*/Models');
        }

        try {
            $files = File::allFiles($path);
        } catch (\Exception $e) {
            return [];
        }

        foreach ($files as $file) {
            if (Str::contains($file->getBasename(), 'Test.php')) {
                continue;
            }

            $filesInfo[] = [
                'filename' => str_ireplace('.php', '', $file->getFilename()),
                'pathname' => $file->getPathname(),
                'namespace' => $this->getAppDomainNamespaceFromRealPath($file->getRealPath()),
            ];
        }

        return $filesInfo;
    }

    protected function getExistingDomainServices()
    {
        return $this->getExistingDomainFilesInfo(self::SERVICES);
    }

    protected function getExistingDomainModels()
    {
        return $this->getExistingDomainFilesInfo(self::MODELS);
    }

    protected function getAppDomainNamespaceFromRealPath(string $realPath)
    {
        return 'App/' . Str::before(Str::after($realPath, 'app/'), '.php');
    }

    protected function getRouteName(array $input): string
    {
        return Str::lower(Str::plural(Str::kebab($input['controller_name'])));
    }
}
