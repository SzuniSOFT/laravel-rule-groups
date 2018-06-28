<?php


namespace SzuniSoft\RuleGroups\Test\RuleGroup\Fixtures;


use Illuminate\Validation\Rule;
use SzuniSoft\RuleGroups\RuleGroup;

class CompanyRuleGroup extends RuleGroup
{

    /**
     * @var array
     */
    protected $attributes;

    /**
     * CompanyRuleGroup constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
        parent::__construct();
    }


    /**
     * Returns with the corresponding rules
     *
     * @return array
     */
    protected function getAttributeRules()
    {
        return $this->attributes;
    }
}