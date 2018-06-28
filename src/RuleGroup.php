<?php


namespace SzuniSoft\RuleGroups;

use ArrayAccess;
use function Couchbase\defaultDecoder;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use InvalidArgumentException;


/**
 * Class CommonRuleGroup
 *
 * @package Modules\Support\Rules
 */
abstract class RuleGroup implements Arrayable, ArrayAccess
{

    const LARAVEL_RULE_SEPARATOR_CHARACTER = '|';

    /**
     * The prefix of group
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * @var array
     */
    protected $attributeRules = [];

    /**
     * The exact copy of the initial state of @see $attributeRules
     *
     * @var array
     */
    protected $restoringAttributeRules = [];

    /**
     * Contains the runtime added extra rules
     *
     * @var array
     */
    protected $additionalRules = [];

    /**
     * Contains rules that should be applied on all attributes
     *
     * @var array
     */
    protected $additionalGlobalRules = [];

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var string
     */
    protected $inline_rule_delimiter;

    /**
     * RuleGroup constructor.
     */
    public function __construct()
    {
        $this->setConfig(app('config'));
        $this->init();
    }


    /**
     * Initialize instance.
     * Leave constructor free and extendable for extender children.
     */
    protected function init()
    {

        /*
         * Set inline rule delimiter defined in the configuration
         * */
        $this->inline_rule_delimiter = $this->config->get('rule-groups.laravel.inline_rule_delimiter');

        /*
         * Fetch and optimize rules
         * */
        $this->fetchRules();

        /*
         * Setup the initial state of runtime group
         * */
        $this->restoringAttributeRules = $this->attributeRules;

    }

    /**
     * Fetches rules
     *
     * @return array
     */
    protected function fetchRules()
    {
        if (empty($this->attributeRules)) {
            $this->attributeRules = array_map(function ($rules) {
                return $this->optimizeRules($rules);
            }, $this->getAttributeRules());
        }

        return $this->attributeRules;
    }

    /**
     * The corresponding rules
     *
     * @return array
     */
    abstract protected function getAttributeRules();

    /**
     * Builds a single attribute rule.
     * it does prefixing.
     * it does injecting additional rules added to attribute.
     *
     * @param string $prefix
     * @param $attribute
     * @param array|string $rules
     * @return array
     */
    protected function buildSingleAttributeRules($prefix = '', $attribute, $rules)
    {

        /*
         * Safely trim the prefix.
         * */
        $prefix = trim($prefix);

        /*
         * Suffixing the prefix as it needs
         * when no ending dot character specified
         * */
        if (! empty($prefix) && ! Str::endsWith($prefix, '.')) {
            $prefix .= '.';
        }

        /*
         * Apply additional rules on attribute
         * */
        if (isset($this->additionalRules[$attribute])) {
            $rules = array_merge($this->additionalRules[$attribute], $rules);
        }


        /*
         * Apply global additional rules
         * */
        if (! empty($this->additionalGlobalRules)) {
            $rules = array_merge($rules, $this->additionalGlobalRules);
        }

        return [$prefix . $attribute => $rules];
    }

    /**
     * Converts inline string rules into arrays
     *
     * @param array|string $rules
     * @return array|string
     */
    protected function optimizeRules($rules)
    {

        if (is_string($rules)) {
            return explode(
                $this->inline_rule_delimiter,
                $rules
            );
        }
        else if (is_object($rules)) {
            return [$rules];
        }
        else if ($rules instanceof Arrayable) {
            return $rules->toArray();
        }

        return $rules;
    }

    /**
     * Builds group and applies rule prefixes
     *
     * @param array $attributes
     * @param string $prefix
     * @return array
     */
    protected function build(array $attributes, $prefix = '')
    {

        $buffer = [];

        foreach ($attributes as $attribute => $rules) {
            $buffer = $buffer + $this->buildSingleAttributeRules($prefix, $attribute, $rules);
        }

        return $buffer;
    }

    /**
     * @param Repository $config
     */
    protected function setConfig(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @return RuleGroup
     */
    protected static function getInstance()
    {
        $instance = new static();

        $instance->setConfig(
            app('config')
        );
        $instance->init();

        return $instance;
    }

    /**
     * Shortcut access of instance
     *
     * @return RuleGroup
     */
    public static function attributes()
    {
        return static::getInstance();
    }

    /**
     * Shortcut access of to array by static invoke
     *
     * @return array
     */
    public static function rules()
    {
        return static::getInstance()->toArray();
    }

    /**
     * Restores the attributes and their rules to the initial state
     *
     * @return $this
     */
    public function restore()
    {
        $this->additionalRules = [];
        $this->additionalGlobalRules = [];
        $this->attributeRules = $this->restoringAttributeRules;
        return $this;
    }

    /**
     * Returns with the current prefix
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * Removes all rules from an attribute
     * but remove attribute itself
     *
     * @param $attribute
     * @return RuleGroup
     */
    public function forgetRulesOf($attribute)
    {
        $this->offsetSet($attribute, []);
        return $this;
    }

    /**
     * Sets the prefix
     *
     * @param string|null $prefix
     * @return RuleGroup
     */
    public function prefixed($prefix)
    {
        $this->prefix = $prefix ?: '';
        return $this;
    }

    /**
     * Adds an attribute and it's rules to the pool
     *
     * @param $attribute
     * @param array|string|object $rules
     * @param bool $overwrite
     * @return RuleGroup
     */
    public function addRulesTo($attribute, $rules, $overwrite = false)
    {

        $rules = $this->optimizeRules($rules);

        $currentRules = $this->offsetExists($attribute)
            ? $this->offsetGet($attribute)
            : [];

        if (! $overwrite) {
            $rules = array_merge(
                $currentRules,
                $rules
            );
        }

        $this->offsetSet(
            $attribute,
            $rules
        );
        return $this;
    }

    /**
     * Overwrites rules of a specific attribute
     *
     * @param $attribute
     * @param $rules
     * @return RuleGroup
     */
    public function overwriteRulesOf($attribute, $rules)
    {
        return $this->addRulesTo($attribute, $rules, true);
    }

    /**
     * Removes an attribute and it's rules
     *
     * @param string $attribute
     * @return $this
     */
    public function without($attribute)
    {

        $attributes = is_array($attribute) ? $attribute : func_get_args();

        foreach ($attributes as $attribute) {
            $this->offsetUnset($attribute);
        }
        return $this;
    }

    /**
     * Apply the given rules on all attributes
     *
     *
     * @param $rules
     * @return RuleGroup
     */
    public function addToAll($rules)
    {
        $this->additionalGlobalRules = $this->additionalGlobalRules + $this->optimizeRules($rules);
        return $this;
    }

    /**
     * Render attribute rule group
     *
     * @return array
     */
    public function toArray()
    {
        return $this->build(
            $this->attributeRules,
            $this->prefix
        );
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributeRules[$offset]);
    }

    /**
     * @param mixed $offset
     * @return string|array
     */
    public function offsetGet($offset)
    {
        return $this->attributeRules[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->attributeRules[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->attributeRules[$offset]);
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function __isset($attribute)
    {
        return $this->offsetExists($attribute);
    }

    /**
     * @param $attribute
     */
    public function __unset($attribute)
    {
        return $this->offsetUnset($attribute);
    }

    /**
     * Static method call wrapper
     *
     * @param $name
     * @param $arguments
     * @return RuleGroup|static
     */
    public static function __callStatic($name, $arguments)
    {
        return static::getInstance()->$name(...$arguments);
    }


}