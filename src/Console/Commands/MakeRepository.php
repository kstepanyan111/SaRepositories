<?php

namespace Sa\Repositories\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--no-filter} {--m|migration} {--u|uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new repository classes';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');

        /*
         * Remove Repository postfix if exist
         */
        $name = str_ireplace('repository', '', $name);

        $repository = Str::studly(class_basename($this->argument('name')));

        $path = config('sa-repositories.path');

        /**
         * Eloquent class
         */
        $this->call('make:repository-eloquent', [
            'name' => $path . $name . '\\' . "Eloquent{$repository}Repository",
        ]);

        /**
         * Repository interface
         */
        $this->call('make:repository-interface', [
            'name' => $path . $name . '\\' . "{$repository}Repository",
        ]);

        if ($this->option('no-filter')) {

            /**
             * Model
             */
            $this->call('make:repository-model', [
                'name' => $path . $name . '\\' . $repository,
                '-u' => $this->option('uuid'),
            ]);
        } else {

            /**
             * Filter class
             */
            $this->call('make:repository-filter', [
                'name' => $path . $name . '\\' . "{$repository}Filter",
            ]);

            /**
             * Model
             */
            $this->call('make:repository-model-filterable', [
                'name' => $path . $name . '\\' . $repository,
                '-u' => $this->option('uuid'),
            ]);
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        $this->info("Repository created!");

        return true;
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model.'],
            ['uuid', 'u', InputOption::VALUE_NONE, 'Use Uuids trait in model (emadadly/laravel-uuid).'],
        ];
    }

}
