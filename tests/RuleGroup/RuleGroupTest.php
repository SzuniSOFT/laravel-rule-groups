<?php

namespace SzuniSoft\RuleGroups\Test\RuleGroup;


use Illuminate\Support\Facades\Validator;
use Orchestra\Testbench\TestCase;
use SzuniSoft\RuleGroups\Providers\RuleGroupServiceProvider;
use SzuniSoft\RuleGroups\Test\RuleGroup\Fixtures\CompanyRuleGroup;
use SzuniSoft\RuleGroups\Test\RuleGroup\Fixtures\PhoneRule;

class RuleGroupTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            RuleGroupServiceProvider::class
        ];
    }


    /**
     * @return array
     */
    protected function rules()
    {
        return [
            'name' => ['required', 'max:255'],
            'regnr' => ['required'],
            'vatnr' => ['required'],
            'country' => ['required'],
            'state' => ['required'],
            'zip' => ['required'],
            'city' => ['required'],
            'address' => ['required']
        ];
    }

    /**
     * @param $rules
     * @return CompanyRuleGroup
     */
    protected function ruleGroup($rules)
    {
        return new CompanyRuleGroup($rules);
    }

    /** @test */
    public function it_has_right_initial_state()
    {

        $desiredRules = [
            'name' => ['required', 'max:255'],
            'regnr' => ['required'],
            'vatnr' => ['required'],
            'country' => ['required'],
            'state' => ['required'],
            'zip' => ['required'],
            'city' => ['required'],
            'address' => ['required']
        ];

        $group = $this->ruleGroup($this->rules());
        $this->assertEquals($desiredRules, $group->toArray());
    }

    /** @test */
    public function it_can_prefix_group()
    {

        $group = $this->ruleGroup(['name' => ['required']]);
        $group->prefixed('billing');

        $this->assertSame('billing', $group->prefix());
        $this->assertEquals(['billing.name' => ['required']], $group->toArray());
    }

    /** @test */
    public function it_can_handle_difficult_prefixes()
    {

        $group = $this->ruleGroup(['name' => ['required']]);
        $group->prefixed('billing.something.other');

        $this->assertSame('billing.something.other', $group->prefix());
        $this->assertEquals(['billing.something.other.name' => ['required']], $group->toArray());
    }

    /** @test */
    public function it_can_normalize_additive_rules()
    {
        $group = $this->ruleGroup(['name' => ['required']]);
        $group->addRulesTo('name', 'min:5|max:30');

        $this->assertEquals([
            'name' => [
                'required',
                'min:5',
                'max:30'
            ]
        ], $group->toArray());
    }

    /** @test */
    public function it_can_normalize_additive_attributes()
    {
        $group = $this->ruleGroup(['name' => 'required|min:20']);

        $this->assertEquals([
            'name' => [
                'required',
                'min:20'
            ]
        ], $group->toArray());
    }

    /** @test */
    public function it_can_restore_initial_state()
    {

        $group = $this->ruleGroup($this->rules());
        $group->without('name');

        $this->assertArrayNotHasKey('name', $group->toArray());

        $group->restore();

        $this->assertArrayHasKey('name', $group->toArray());
    }

    /** @test */
    public function it_can_forget_rules()
    {

        $group = $this->ruleGroup(['name' => ['required']]);
        $group->forgetRulesOf('name');
        $this->assertEquals(['name' => []], $group->toArray());
    }

    /** @test */
    public function it_can_add_new_attribute()
    {
        $group = $this->ruleGroup([]);
        $group->addRulesTo('name', ['required']);
        $this->assertEquals(['name' => ['required']], $group->toArray());
    }

    /** @test */
    public function it_can_add_rules_to_existing_attributes()
    {

        $group = $this->ruleGroup(['name' => ['required']]);
        $group->addRulesTo('name', 'max:255');

        $this->assertEquals([
            'name' => [
                'required',
                'max:255'
            ]
        ], $group->toArray());
    }

    /** @test */
    public function it_can_forget_rules_of_attribute()
    {

        $group = $this->ruleGroup(['name' => ['required']]);
        $group->forgetRulesOf('name');

        $this->assertTrue(empty($group->toArray()['name']));
    }

    /** @test */
    public function it_can_get_rid_of_attribute()
    {

        $group = $this->ruleGroup(['name' => ['required']]);
        $group->without('name');

        $this->assertTrue(empty($group->toArray()));
    }

    /** @test */
    public function it_can_get_rid_of_multiple_attributes()
    {

        $group = $this->ruleGroup($this->rules());
        $group->without('name', 'country', 'city');

        $result = array_keys($group->toArray());

        $this->assertEquals([
            'regnr', 'vatnr', 'state', 'zip', 'address'
        ], $result);
    }


    /** @test */
    public function it_can_overwrite_existing_rules_of_attribute()
    {

        $group = $this->ruleGroup($this->rules());
        $group->overwriteRulesOf('name', ['sometimes', 'min:5']);

        $this->assertEquals(
            ['sometimes', 'min:5'],
            $group->toArray()['name']
        );
    }

    /** @test */
    public function it_can_apply_rules_on_all_attributes()
    {

        $group = $this->ruleGroup($this->rules());
        $group->addToAll(['min:5']);

        $this->assertEquals([
            'name' => ['required', 'max:255', 'min:5'],
            'regnr' => ['required', 'min:5'],
            'vatnr' => ['required', 'min:5'],
            'country' => ['required', 'min:5'],
            'state' => ['required', 'min:5'],
            'zip' => ['required', 'min:5'],
            'city' => ['required', 'min:5'],
            'address' => ['required', 'min:5']
        ], $group->toArray());
    }

    /** @test */
    public function it_can_handle_object_rules()
    {

        $rule = new PhoneRule();

        $group = $this->ruleGroup(['phone' => ['required']]);
        $group->addRulesTo('phone', $rule);

        $this->assertSame($rule, $group->toArray()['phone'][1]);
    }

}