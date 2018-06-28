<?php


namespace SzuniSoft\RuleGroups\Providers;


use Illuminate\Support\ServiceProvider;
use SzuniSoft\RuleGroups\Console\RuleGroupMakeCommand;

/**
 * Class ArtisanServiceProvider
 * @package SzuniSoft\RuleGroups
 */
class ArtisanServiceProvider extends ServiceProvider
{

    /*
     * Indicates the provider is deferred
     * */
    protected $defer = true;

    protected $commands = [
        RuleGroupMakeCommand::class => 'command.rule.group.make'
    ];

    /**
     * Register service provider
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Register rule group make command
     */
    protected function registerRuleGroupMakeCommand()
    {

        $this->app->singleton('command.rule.group.make', function ($app) {
            return new RuleGroupMakeCommand($app['config'], $app['files']);
        });

    }

    /**
     * Registers commands
     */
    protected function registerCommands()
    {

        foreach (array_keys($this->commands) as $command) {
            $command = class_basename($command);
            call_user_func([$this, "register{$command}"]);
        }

        $this->commands(array_values($this->commands));
    }

    /**
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }

}