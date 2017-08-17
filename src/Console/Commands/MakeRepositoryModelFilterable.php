<?php

namespace Sa\Repositories\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class MakeRepositoryModelFilterable extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository-model-filterable {name} {--u|uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    protected $type = "Repository model";

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Override build class
     *
     * @param string $name
     * @return mixed
     */
    public function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceFilterClassName($stub, $name)
            ->removeUuidsTrait($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository-model-filterable.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $namespace = str_replace('App\\', '', $this->getNamespace($name));
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace'],
            [$namespace, $this->rootNamespace()],
            $stub
        );

        return $this;
    }

    public function replaceFilterClassName(&$stub, $name)
    {

        $stub = str_replace(
            'DummyFilter',
            class_basename(str_plural($name) . "Filter"),
            $stub
        );

        return $this;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    protected function removeUuidsTrait(&$stub, $name)
    {
        if (!$this->option('uuid')) {
            $stub = str_replace(
                [
                    'use Emadadly\LaravelUuid\Uuids;',
                    'use Uuids;',
                    'public $incrementing = false;'
                ],
                [
                    '',
                    '',
                    'public $incrementing = true;'
                ],
                $stub
            );
        } else {
            $stub = str_replace(
                [
                    'public $incrementing = true;'
                ],
                [
                    'public $incrementing = false;'
                ],
                $stub
            );
        }

        return $this;
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['uuid', 'u', InputOption::VALUE_NONE, 'Use Uuids trait in model (emadadly/laravel-uuid).'],
        ];
    }

}
