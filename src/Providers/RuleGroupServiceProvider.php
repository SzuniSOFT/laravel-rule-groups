<?php


namespace SzuniSoft\RuleGroups\Providers;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use SzuniSoft\RuleGroups\RuleGroup;


/**
 * Class RuleGroupServiceProvider
 * @package SzuniSoft\RuleGroups\Providers
 */
class RuleGroupServiceProvider extends ServiceProvider
{

    /**
     * Register service provider
     */
    public function register()
    {
        $this->registerConfig();
        $this->app->register(ArtisanServiceProvider::class);
    }

    /**
     * Boot service provider
     */
    public function boot()
    {

        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('rule-groups.php')
        ], 'config');

    }

    /**
     * Registers configuration
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'rule-groups');
    }

}