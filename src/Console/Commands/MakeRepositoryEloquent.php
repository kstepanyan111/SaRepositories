<?php

namespace Sa\Repositories\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;

class MakeRepositoryEloquent extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository-eloquent {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $type = "Repository eloquent";

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository-eloquent.stub';
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
            ->replaceRepositoryClassName($stub, $name)
            ->replaceModelClassName($stub, $name)
            ->replaceClass($stub, $name);
    }

    protected function replaceRepositoryClassName(&$stub, $name)
    {
        $repositoryClassName = str_replace('Eloquent', '', class_basename($name));

        $stub = str_replace(
            'DummyRepositoryInterface',
            $repositoryClassName,
            $stub
        );

        return $this;
    }

    protected function replaceModelClassName(&$stub, $name)
    {

        $modelClassName = str_replace('Eloquent', '', class_basename($name));
        $modelClassName = str_replace('Repository', '', $modelClassName);
        $modelClassName = str_singular($modelClassName);

        $stub = str_replace(
            'DummyModelClass',
            $modelClassName,
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

}
