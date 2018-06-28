# Rule Groups

With Laravel it's very easy to run predefined validation rules and customize them. It is also possible to create your own rule by several ways.

One of our projects uses a tons of validations and most of them are the same or very similar to each other.
This package provides an easy way to make reusable validation rule groups.

The prime advantage of this package is the code reusability and centralized rule group controlling and managing.

## Installing

```
composer require szunisoft/laravel-rule-groups
``` 
If you are on lower version of Laravel and you don't have package discovery yet please add the *ServiceProvider* to the ```config/app.php``` configuration file.

```php
'providers' => [
    ...
    
    /*
     * Package Service Providers...
     */
     \SzuniSoft\RuleGroups\Providers\RuleGroupServiceProvider::class
]
```

## Configuration
By default the package will generate all rule groups to the **app/RuleGroups** directory. You can change it by publishing the configuration file.

```
php artisan vendor:publish --provider="\SzuniSoft\RuleGroups\Providers\RuleGroupServiceProvider" --tag="config"
```

## Creating Rule Groups

```
php artisan make:rule-group CompanyRuleGroup
```

## Writing Rule Groups

Locate the generated class. Default location is **app/RuleGroups**.

```
use SzuniSoft\RuleGroups\RuleGroup;

class CompanyRuleGroup extends RuleGroup {

    protected function getAttributeRules() {
        
        // Here we go..
        
        return [
        
            // This is self explained
            'name' => ['required'],
            
            // You don't have to take care of when use or not to use inline formats
            'vat_number' => 'required|min:5',
            
            // You can use your custom validation rules 
            // just like you normally would.
            'phone' => new MyVeryCustomAndFavoritePhoneRule(),
            
            // Laravel built in Rule is welcomed too
            'country' => ['required', Rule::exists('countries', 'iso_2')],
        ];
    }

}
```

## Basic Usage - Reusability

You can easily use rule groups in your controllers. See the example.

```php
class RegisterController {
    use ValidatesRequests;

    /**
     * Hey there Mr. Request! I'm watching you!
     * /
    public function register (Request $request) {
    
        $this->validate($request, CompanyRuleGroup::rules());
        
        // Further NASA-like secret business logic..
    
    } 
}
```
This will be equivalent with the following:

```php
class RegisterController {
    use ValidatesRequests;

    /**
     * Hey there Mr. Request! I'm watching you!
     * /
    public function register (Request $request) {
    
        $this->validate($request, [
            'name' => ['required'],
            'vat_number' => ['required', 'min:5'],
            'phone' => new MyVeryCustomAndFavoritePhoneRule(),
            'country' => ['required', Rule::exists('countries', 'iso_2')],
        ]);
        
        // Further NASA-like secret business logic..
    
    } 
}
```

Now you can use this validation group in any other controllers or wherever you want to validate.

## Advanced Usage - On demand configuration

In this chapter we'll take a closer look on how we can modify these groups in extremist situations.

Let's say we have a registration page where the user must specify the information of the managed company but we also need billing information.
That's okay, easy and simple. But what if the managed company and the billing company are not the same one?

Take a look on the following **Rule Group**

```php
use SzuniSoft\RuleGroups\RuleGroup;

class CompanyRuleGroup extends RuleGroup {

    protected function getAttributeRules() {
        
        return [
            'name' => ['required'],
            'vat_number' => ['required', 'min:5'],
            'phone' => new MyVeryCustomAndFavoritePhoneRule(),
            'country' => ['required', Rule::exists('countries', 'iso_2')],
            'state' => ['required', 'max:50'],
            'city' => ['required', 'max:50'],
            'zip_code' => ['required', 'max:50'],
            'address' => ['required', 'max:250']
        ];
    }

}
```

Now let's use it in our controller

```php
public function register (Request $request) {

    $this->validate($request, array_merge(
        CompanyRuleGroup::attributes()->prefixed('managed')->toArray(),
        CompanyRuleGroup::attributes()->prefixed('billed')->toArray(),
    ));

}
```
This will turn into this

```php
public function register (Request $request) {

    $this->validate($request, [
        
        'managed.name' => ['required'],
        'managed.vat_number' => ['required' ,'min:5'],
        'managed.phone' => new MyVeryCustomAndFavoritePhoneRule(),
        'managed.country' => ['required', Rule::exists('countries', 'iso_2')],
        'managed.state' => ['required', 'max:50'],
        'managed.city' => ['required', 'max:50'],
        'managed.zip_code' => ['required', 'max:50'],
        'managed.address' => ['required', 'max:250'],
        
        'billed.name' => ['required'],
        'billed.vat_number' => ['required', 'min:5'],
        'billed.phone' => new MyVeryCustomAndFavoritePhoneRule(),
        'billed.country' => ['required', Rule::exists('countries', 'iso_2')],
        'billed.state' => ['required', 'max:50'],
        'billed.city' => ['required', 'max:50'],
        'billed.zip_code' => ['required', 'max:50'],
        'billed.address' => ['required', 'max:250'],
    
    ]);

}
```

Let's say it's no enough for us and we need the nearly same rules but not exactly.

We want to validate the billed company inputs only when the user wants different billing company.

```php
public function register (Request $request) {

    $this->validate($request, array_merge(
        CompanyRuleGroup::attributes()->prefixed('managed')->toArray(),
        CompanyRuleGroup::attributes()
        
            // Adds rule(s) to all attribute in the group
            ->addToAll('required_without:user_wants_managed_as_billed')
            
            ->prefixed('billed')
            ->toArray(),
    ));

}
```

### Available methods
After using the ```attributes()``` static method you can use the following ones:

Attribute management
- ``addRulesTo($attribute, $rules)`` Adds new rules to the attribute. Attribute will be created if not exists.
- ``overwriteRulesOf($attribute, $rules)`` Overwrites rules of a specific attribute.
- ``without($attribute)`` Removes an attribute and it's rules from the group.
- ``forgetRulesOf($attribute)`` Removes all rules from an attribute
- ``addToAll($rules)`` Apply the given rules on all attributes

Utility
- ``restore()`` Restores the group to it's initial state.
- ``prefix()`` Applies prefix on all attribute. You can use deep dotting array access pattern *(x.y.z)*

Don't forget to invoke the ```toArray()``` method.