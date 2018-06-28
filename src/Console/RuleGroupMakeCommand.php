<?php


namespace SzuniSoft\RuleGroups\Console;


use Illuminate\Config\Repository;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class RuleGroupMakeCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:rule-group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create rule group';

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Rule Group';

    /**
     * RuleGroupMakeCommand constructor.
     * @param Repository $repository
     * @param Filesystem $filesystem
     */
    public function __construct(Repository $repository, Filesystem $filesystem)
    {
        parent::__construct($filesystem);
        $this->repository = $repository;
    }


    /**
     * Create rule group
     */
    public function handle()
    {

        if (parent::handle() === false && ! $this->option('force')) {
            return;
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function buildClass($name)
    {

        $class = parent::buildClass($name);

        return $class;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\' . $this->repository->get('rule-groups.directory', 'RuleGroups');
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/rule_group.stub';
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the notification already exists.'],
        ];
    }


}