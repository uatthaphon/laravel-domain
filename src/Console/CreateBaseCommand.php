<?php

namespace Uatthaphon\LaravelDomain\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateBaseCommand extends BaseCommand
{
    protected $signature = 'laravel-domain:base';

    protected $description = 'Create Base Domain Driven Design structure.';

    public array $createdFiles = [];

    public function handle()
    {
        $input = [
            'base_domain' => '',
            'sub_domain' => '',
        ];
        $output = '';

        $this->info('Running Base Domain Driven Design structure Generator...');

        $input['base_domain'] = $this->ask('Please Name your Base Domain');
        $input['base_domain'] = str_replace(' ', '', $input['base_domain']);

        if (empty($input['base_domain'])) {
            $this->comment('You not given any domain, We assume that you are not interest in create new domain. then see you next time.');
            return;
        }

        $input['sub_domain'] = $this->ask('Please Name your Sub Domain', $input['base_domain']);
        $input['sub_domain'] = str_replace(' ', '', $input['sub_domain']);

        $input = $this->capitalDomainValues($input);

        $output = $this->callMakeMigration($input);
        $this->outputLine($output);

        $output = $this->callMakeFactory($input);
        $this->outputLine($output);

        $output = $this->callMakeSeeder($input);
        $this->outputLine($output);

        $output = $this->callMakeModel($input);
        $this->outputLine($output);

        $output = $this->createRepositoryFile($input);
        $this->outputLine($output);

        $output = $this->createdServiceFile($input);
        $this->outputLine($output);

        $output = $this->createServiceTestFile($input);
        $this->outputLine($output);
    }

    protected function callMakeMigration(array $input): string
    {
        $name = 'create_' . Str::snake(Str::plural($input['sub_domain'])) . '_table';

        try {
            Artisan::call("make:migration {$name}");

            $this->createdFiles['migration'] = str_replace(
                ["\r", "\n"],
                '',
                Str::after(Artisan::output(), 'Created Migration: ')
            ) . '.php';

            return $this->replaceNewLineToEmpty('Create Migration: ' . $this->createdFiles['migration']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function callMakeFactory(array $input): string
    {
        $path = 'Domain/' . $input['base_domain'];
        $name = $path . '/' . $input['sub_domain'];

        try {
            Artisan::call("make:factory {$name} --model=" . $name);

            $destinationDirectory = database_path('factories/' . $path);
            $destinationFile = $destinationDirectory . '/' . $input['sub_domain'] . 'Factory' . '.php';
            $search = 'App\Models\Domain\\' . $input['base_domain'] . '\\' . $input['sub_domain'];
            $replace = 'App\Domain\\' . $input['base_domain'] . '\Models\\' . $input['sub_domain'];

            $content = File::get($destinationFile);
            $count = -1;
            $content = str_replace(
                $search,
                $replace,
                $content,
                $count
            );

            File::put($destinationFile, $content);

            $this->createdFiles['factory'] = $destinationFile;

            return $this->replaceNewLineToEmpty('Create Factory: ' . $this->createdFiles['factory']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function callMakeSeeder(array $input): string
    {
        $path = 'Domain/' . $input['base_domain'];
        $name = $input['sub_domain'] . 'Seeder';

        try {
            Artisan::call("make:seeder {$name}");

            $currentFile = database_path('seeders/' . $name . '.php');
            $destinationDirectory = database_path('seeders/' . $path);
            $destinationFile = $destinationDirectory . '/' . $name . '.php';

            if (! File::exists($destinationDirectory)) {
                File::makeDirectory($destinationDirectory, 0755, true);
            }

            File::move($currentFile, $destinationFile);

            $content = File::get($destinationFile);
            $content = str_replace(
                'Database\Seeders',
                'Database\Seeders\Domain\\' . $input['base_domain'],
                $content
            );

            File::put($destinationFile, $content);

            $this->createdFiles['seeder'] = $destinationFile;

            return $this->replaceNewLineToEmpty('Create Seeder: ' . $this->createdFiles['seeder']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    // app
    protected function callMakeModel(array $input): string
    {
        $path = 'Domain/' . $input['base_domain'] . '/Models/' . $input['sub_domain'];
        $name = '/App/' . $path;

        try {
            Artisan::call("make:model {$name}");

            $this->createdFiles['model'] = app_path($path . '.php');

            return $this->replaceNewLineToEmpty('Create Model: ' . $this->createdFiles['model']);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    protected function createRepositoryFile(array $input): string
    {
        $stubPath = __DIR__ . '/../../stubs/Repo.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::REPOSITORIES, $input),
            '{{ namespacedModel }}' => $this->getNamespaceModel($input),
            '{{ class }}' => $this->getClass(self::REPOSITORIES, $input),
            '{{ model }}' => $this->getModel($input),
            '{{ modelVariable }}' => $this->getModelVariable($input),
        ];
        $type = self::REPOSITORIES;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Repository file!!';
        }

        $this->createdFiles['repository'] = $this->getAppDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Create Repository: ' . $this->createdFiles['repository'];
    }

    protected function createdServiceFile(array $input): string
    {
        $stubPath = __DIR__ . '/../../stubs/Service.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::SERVICES, $input),
            '{{ namespacedModel }}' => $this->getNamespaceModel($input),
            '{{ namespacedRepository }}' => $this->getNamespacedRepository($input),
            '{{ class }}' => $this->getClass(self::SERVICES, $input),
            '{{ model }}' => $this->getModel($input),
            '{{ modelVariable }}' => $this->getModelVariable($input),
            '{{ repository }}' => $this->getClass(self::REPOSITORIES, $input),
            '{{ repositoryVariable }}' => $this->getRepositoryVariable($input),
        ];
        $type = self::SERVICES;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Service file!!';
        }

        $this->createdFiles['service'] = $this->getAppDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Create Service: ' . $this->createdFiles['service'];
    }

    protected function createServiceTestFile(array $input): string
    {
        $stubPath = __DIR__ . '/../../stubs/ServiceTest.stub';
        $replacements = [
            '{{ namespace }}' => $this->getNamespace(self::SERVICES, $input),
            '{{ namespacedModel }}' => $this->getNamespaceModel($input),
            '{{ namespacedRepository }}' => $this->getNamespacedRepository($input),
            '{{ class }}' => $this->getClass(self::SERVICES_TEST, $input),
            '{{ model }}' => $this->getModel($input),
            '{{ modelVariable }}' => $this->getModelVariable($input),
            '{{ service }}' => $this->getClass(self::SERVICES, $input),
            '{{ table }}' => $this->getTable($input),
        ];
        $type = self::SERVICES_TEST;

        if (! $this->createStub($stubPath, $input, $replacements, $type)) {
            return 'Fail to create Service Test file!!';
        }

        $this->createdFiles['service_test'] = $this->getAppDirectory($type, $input) . '/' . $this->getFileName($type, $input);

        return 'Create Service Test: ' . $this->createdFiles['service_test'];
    }
}
