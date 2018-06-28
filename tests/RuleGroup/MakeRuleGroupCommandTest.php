<?php


namespace SzuniSoft\RuleGroups\Test\RuleGroup;


use Orchestra\Testbench\TestCase;
use SzuniSoft\RuleGroups\Providers\ArtisanServiceProvider;
use SzuniSoft\RuleGroups\Providers\RuleGroupServiceProvider;

class MakeRuleGroupCommandTest extends TestCase
{

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $finder;

    /**
     * @var string
     */
    protected $ruleGroupsPath;

    protected function setUp()
    {
        parent::setUp();

        $this->ruleGroupsPath = app_path('RuleGroups');
        $this->finder = $this->app['files'];
    }

    protected function getPackageProviders($app)
    {
        return [
            RuleGroupServiceProvider::class,
            ArtisanServiceProvider::class
        ];
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->finder->deleteDirectory($this->ruleGroupsPath);
    }

    /** @test */
    public function it_can_create_rule_group_class()
    {

        $name = 'CompanyRuleGroup';

        $this->artisan('make:rule-group', [
            'name' => $name
        ]);

        $this->assertFileExists($this->ruleGroupsPath . "/{$name}.php");
    }

    /** @test */
    public function it_can_use_different_namespace()
    {

        $name = 'SubNamespace/CompanyRuleGroup';

        $this->artisan('make:rule-group', [
            'name' => $name
        ]);

        $this->assertFileExists($this->ruleGroupsPath . "/{$name}.php");
    }

}