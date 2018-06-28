<?php


namespace SzuniSoft\RuleGroups\Test\RuleGroup;


use Orchestra\Testbench\TestCase;
use SzuniSoft\RuleGroups\Providers\RuleGroupServiceProvider;

class RuleGroupServiceProviderTest extends TestCase
{

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $finder;

    /**
     * @var string
     */
    protected $configFilePath;

    protected function getPackageProviders($app)
    {
        return [
            RuleGroupServiceProvider::class
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->configFilePath = config_path('rule-groups.php');
        $this->finder = $this->app['files'];
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->finder->delete($this->configFilePath);
    }

    /** @test */
    public function it_can_publish_configuration_file()
    {

        $this->artisan('vendor:publish', [
            '--provider' => RuleGroupServiceProvider::class,
            '--tag' => 'config'
        ]);

        $this->assertFileExists($this->configFilePath);

    }

}