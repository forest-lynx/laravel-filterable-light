<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Providers;

use ForestLynx\FilterableLight\Console\Commands\CreateFilterableDataCommand;
use ForestLynx\FilterableLight\Exceptions\UnsupportedDbDriver;
use ForestLynx\FilterableLight\Schema\Contracts\SchemaContract;
use ForestLynx\FilterableLight\Schema\SchemaMysql;
use Illuminate\Support\ServiceProvider;

class FilterableServiceProvider extends ServiceProvider
{
    protected $namespace = "filterable-light";

    public function register()
    {
        parent::register();
    }

    public function boot()
    {
        /* Add config file */
        $this->mergeConfigFrom([
            __DIR__ . "/../../config/{$this->namespace}.php" => \config_path("{$this->namespace}.php"),
        ], 'filterable-config');
    }
}
