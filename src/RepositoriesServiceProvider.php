<?php

namespace Sa\Repositories;

use Illuminate\Support\ServiceProvider;
use Sa\Repositories\Console\Commands\MakeRepository;
use Sa\Repositories\Console\Commands\MakeRepositoryEloquent;
use Sa\Repositories\Console\Commands\MakeRepositoryFilter;
use Sa\Repositories\Console\Commands\MakeRepositoryInterface;
use Sa\Repositories\Console\Commands\MakeRepositoryModel;
use Sa\Repositories\Console\Commands\MakeRepositoryModelFilterable;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/sa-repositories.php' => config_path('sa-repositories.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRepository::class,
                MakeRepositoryEloquent::class,
                MakeRepositoryFilter::class,
                MakeRepositoryInterface::class,
                MakeRepositoryModel::class,
                MakeRepositoryModelFilterable::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
