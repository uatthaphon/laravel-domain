**Table of contents**

- [Laravel Domain tools TODO](#laravel-domain-tools-todo)
  - [FIRST COMMAND](#first-command)
  - [CREATE DOMAIN GENERATE COMMAND](#create-domain-generate-command)
  - [CREATE DOMAIN API GENERATE COMMAND](#create-domain-api-generate-command)
  - [CREATE DOMAIN CRUD GENERATE COMMAND](#create-domain-crud-generate-command)

# Laravel Domain tools TODO

## FIRST COMMAND

- [x] First we need to run install command to get config file for setup our folder structure for other commands

```sh
    php artisan laravel-domain:install
```

## CREATE DOMAIN GENERATE COMMAND

```sh
    php artisan laravel-domain:base
```
<!-- Database -->
- [x] Generate Factories`database/factories/Domain/<domain_name>`
  - `<domain_name>Factory.php`
- [x] Generate Migrations `database/migrations`
  - `<Y_m_d_His>_create_<domain>_table.php`
- [x] Generate Seeder `database/seeders/Domain/<domain_name>`
  - `<domain_name>Seeder.php`

<!-- App -->
- [x] Generate Models`app/Domain/<domain_name>/Models`
  - `<domain_name>.php`
- [x] Generate Repositories `app/Domain/<domain_name>/Repositories`
  - `<domain_name>Repo.php`
- [x] Generate Services`app/Domain/<domain_name>/Services`
  - `<domain_name>Service.php`
  - `<domain_name>ServiceTest.php`

## CREATE DOMAIN API GENERATE COMMAND

```sh
    php artisan laravel-domain:api
```

- Generate Api Controller  `app/Http/Controllers/Api`
  - `<domain_name>Controller.php`
  - `<domain_name>ControllerTest.php`
- Generate Api Requests `app/Http/Requests/Api/<domain_name>Controller`
  - `IndexRequest.php`
  - `StoreRequest.php`
  - `UpdateRequest.php`
- Generate Api Resources `app/Http/Resources/Api/<domain_name>`
  - `<domain_name>Resource.php`
  - `<domain_name>ResourceTest.php`
  - `<domain_name>ResourceCollection.php`
- Generate or Update Api routes for api controller with all resources

## CREATE DOMAIN CRUD GENERATE COMMAND

```sh
    php artisan laravel-domain:crud
```

- Check and update required libs in package.json and composer.json (ex. tailwindcss for package.json and livewire-datatables for composer.json)
- Command promp Ask if we need to Generate pre property from table name `DB::select('describe table_name');`
- Command promp Ask Folder Destination alternative from `web` folder
- Generate Web Controller `app/Http/Controllers/<destination_folder_name>`
  - `<domain_name>Controller.php` with --resources (index,create,store,show,edit,update,destroy)
  - `<domain_name>ControllerTest.php` with --resources (index,create,store,show,edit,update,destroy)
- Generate Web Requests `app/Http/Requests/<destination_folder_name>/<domain_name>Controller`
  - `StoreRequest.php`
  - `UpdateRequest.php`
- Generate Livewire Http Component `app/Http/Livewire/<destination_folder_name>/<domain_name>Controller`
  - `IndexForm.php`
  - `CreateForm.php`
  - `ShowForm.php`
  - `EditForm.php`
- Generate Livewire Resources Component `resources/views/livewire/<destination_folder_name>/<domain_name>Controller`
  - `index_form.blade.php`
  - `create_form.blade.php`
  - `show_form.blade.php`
  - `edit_form.blade.php`
