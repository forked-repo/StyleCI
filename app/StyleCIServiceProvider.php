<?php

/**
 * This file is part of StyleCI by Graham Campbell.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 */

namespace GrahamCampbell\StyleCI;

use Illuminate\Support\ServiceProvider;
use Lightgear\Asset\Asset;

/**
 * This is the styleci service provider class.
 *
 * @author    Graham Campbell <graham@mineuk.com>
 * @copyright 2014 Graham Campbell
 * @license   <https://github.com/GrahamCampbell/StyleCI/blob/master/LICENSE.md> AGPL 3.0
 */
class StyleCIServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('graham-campbell/styleci', 'graham-campbell/styleci', __DIR__);

        $this->setupAssets($this->app['asset'], $this->app['config']['laravel-debugbar::enabled']);
    }

    /**
     * Setup the assets.
     *
     * @param \Lightgear\Asset\Asset $asset
     * @param bool                   $debug
     *
     * @return void
     */
    protected function setupAssets(Asset $asset, $debug = false)
    {
        $styles = ['css/styleci-main.css'];

        if ($debug) {
            $styles[] = 'maximebf\debugbar\src\DebugBar\Resources\vendor\highlightjs\styles\github.css';
        }

        $asset->registerStyles($styles, '', 'main');

        $scripts = ['js/styleci-main.js'];

        if ($debug) {
            $scripts[] = 'maximebf\debugbar\src\DebugBar\Resources\vendor\highlightjs\highlight.pack.js';
        }

        $asset->registerScripts($scripts, '', 'main');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModelFactory();
        $this->registerGitHubStatus();
        $this->registerAnalyser();
    }

    /**
     * Register the model factory class.
     *
     * @return void
     */
    protected function registerModelFactory()
    {
        $this->app->singleton('styleci.modelfactory', function ($app) {
            return new Factories\ModelFactory();
        });

        $this->app->alias('styleci.modelfactory', 'GrahamCampbell\StyleCI\Factories\ModelFactory');
    }

    /**
     * Register the github status class.
     *
     * @return void
     */
    protected function registerGitHubStatus()
    {
        $this->app->singleton('styleci.status', function ($app) {
            $github = $app['github']->connection()->repos()->statuses();
            $url = asset('commits');

            return new GitHub\Status($github, $url);
        });

        $this->app->alias('styleci.status', 'GrahamCampbell\StyleCI\GitHub\Status');
    }

    /**
     * Register the analyser class.
     *
     * @return void
     */
    protected function registerAnalyser()
    {
        $this->app->singleton('styleci.analyser', function ($app) {
            $fixer = $app['fixer'];
            $status = $app['styleci.status'];
            $queue = $app['queue.connection'];
            $mailer = $app['mailer'];

            return new Analyser($fixer, $status, $queue, $mailer);
        });

        $this->app->alias('styleci.analyser', 'GrahamCampbell\StyleCI\GitHub\Analyser');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'styleci.analyser',
            'styleci.status',
            'styleci.modelfactory',
        ];
    }
}