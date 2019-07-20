# earc/component-di

The next generation of dependency injection is here to support you in your 
strive for high quality software.

earc/component-di is build on the top of [earc/di](https://github.com/Koudela/eArc-di), 
makes the components of your app explicit and gives you the freedom 
to restrict dependency access class wise to improve the decoupling of your components.

## why should you use it?

[earc/di](https://github.com/Koudela/eArc-di) is a really amazing, lightweight, 
easy to use dependency injection system that integrates even with symfony. 

earc/components-di is earc/di++. You decide if classes are accessible from other 
components or not. It throws an error if you introduce an new dependency accidentally 
and thus helps you to keep the architectural design clean.

Real world example: Assume you maintain as a software engineer several web shops
written mainly by your predecessors. Today your task is to write a new order export
for one of these. Currently you need to pass the shipping costs to an object. You
have an order which has a cart and there is a CartService::getShippingCost() method.
Hurray, that was easy! 

It passes code review, unit testing, integration testing, testing of your customer.
But surprise surprise, you just introduced a new bug. If a customer confirms an
order, the shop operator changes the shipping costs and then the order get exported
the order get exported with the new shipping costs - but the customer has confirmed 
the old ones.

Maybe your lucky and the time window is small enough to let the bug never really 
happen. But face it, you written bad  buggy code and what makes it even worse, it 
passed all quality enforcement measures!

To cut a long story short, if the engineer who wrote the CartService::class had a 
tool to tell the dependency injection system that this service only belong to the 
cart component and its descendants, you would have had no chance to introduce this
bug.

## to what effort?

Nearly none.

You first define your components. That are just empty classes inheriting direct or
indirect from the RootComponent::class.

For each of your classes you decide is it an `public`, `protected` or `private` Service
or no Service at all. For each decision exists an separate Interface.

You decide to which component your class belong. The components class name you
return in a method named `getComponent`.

And last but not least instead of 

```PHP
$this->firstService = di_get(FirstService::class);
$this->secondService = di_get(SecondService::class);
$this->parameter = di_param('some.parameter.key')
``` 

you write 

```PHP
di_comp(static::class)
    ->get($this->firstService, FirstService::class)
    ->get($this->secondService, SecondService::class)
    ->param($this->parameter, 'some.parameter.key');
```

Maybe that sounds much but it is at most a minute more work on each class.

## installation

```php
composer require earc/component-di
```

## bootstrap 

```php
use eArc\ComponentDI\ComponentDI;

ComponentDI::init();
```

## usage

### defining components

Lets stay to the shop example from above. You have `products`, a `cart`, a `wish list`,
a `financial service provider`, a`checkout`, a `shop operator page tool`, an `third
party inventory controll interface`, an `product import` and an `order export` to
make the list short.

Your Component definition could look like this:

```php
class ProductComponent extends RootComponent {}
class CartComponent extends ProductComponent {}
class WishListComponent extends CartComponent {}

class FinancialServiceProviderComponent extends RootComponent {}
class CheckoutComponent extends FinancialServiceProvider {}

class PagesComponent extends RootComponent {}

class ThirdPartyInventoryControllInterfaceComponent extends RootComponent {}
class ProductImportComponent extends ThirdPartyInventoryControllInterfaceComponent {}
class OrderExportComponent extends ThirdPartyInventoryControllInterfaceComponent {}
```

Your pages component need to arrange products. That's no problem. Maybe the products
component has a public service to deliver the data.

It is not about building walls, its about encapsulation. It enables you to divide your
monolith into responsibilities that have some sort of api without breaking it into
microservices.

### the component interface flags

To become part of the component biosphere a class has to implement one of the following
component interfaces: `PublicServiceInterface`, `ProtectedServiceInterface`, `PrivateServiceInterface`
or `NoServiceInterface`. It flags the class with the desired visibility.

To get a grip on the component the interface enforces an `getComponent` method.

```php
class CartService implements ProtectedServiceInterface
{
    ... 
    public static function getComponent(): string
    {
        return CartComponent::class; 
    }
}
```

Since the `CartService::class` is a protected Service it would be visible to the 
classes of the cart component and the wish list component only. 

### the component resolver

`di_comp` returns the component resolver object that belongs to the passed class
name.

**Important:** Use always `di_comp(static::class)` instead of `di_comp(self::class)`
or `di_comp(ClassName::class)`. If the class is extended only the late static binding
of `static::class` ensures that php passes the right argument.  

The component resolver object has three methods `get()` `make()` and `param()`,
They all return the component resolver object to allow method chaining and the
the first argument is a always the variable to pass the result of the call to.
 
### dependency injection and the component resolver 

The component resolver object methods `get()` and `make()` replace the `di_*` functions 
`di_get` and `di_make` respectively. Both perform a visibility check for the
passed class name or the decorator if decorated against the current component first. If 
the visibility check fails a `AccessDeniedException` is thrown. If it is passed
the result of a `di_get` or `di_make` call is written to the `$object` argument that 
was passed by reference.

Example: Using earc/di you write 

```php
__construct() {
    $this->firstService = di_get(FirstService::class);
    $this->secondService = di_get(SecondService::class);
}
``` 

the same using earc/component-di 

```php
__construct() {
    di_comp(static::class)
        ->get($this->firstService, FirstService::class)
        ->get($this->secondService, SecondService::class);
}
```
 
You can check access by 

```php
di_comp_has_access(static::class, Service::class)
```

### parameter injection and the component resolver

The component resolver object method `param()` does not constrict access like `get()`
and `make()` it enriches it. Each components class name is mapped to a key. You
can check it by
 
```php
di_comp_key(YourComponent::class)
```

This key is prefixed. First for the current component, if no parameter is found
for the parent and so on. At last the parameter is looked up without prefix.

If a parameter is found it is written to the `$parameter` argument that was passed
by reference. 

Example: Using earc/di you write 
         
 ```php
__construct() {
    $this->parameter = di_get('serivce.param');
}

``` 

the same using earc/component-di 
         
```php
__construct() {
    di_comp(static::class)
        ->param($this->parameter, 'serivce.param');
}
```

Assuming this peace of code is from a class of the cart component. Then the first
parameter lookup would be `cartcomponent.service.param`, the second would be
`productcomponent.service.param` and the last would be `service.param`.

### decoration, tagging, mocking

The other `di_*` functions for decoration, tagging and mocking stay the same and
can be used without any restrictions. 

`di_clear_cache` is not restricted either. But `di_has` should be used with care - 
maybe sometimes an `di_has(...) && di_comp_has_access(...)` is closer to the truth.

## exceptions

 * All exceptions thrown inherit from `eArc\DI\BaseException`.
 
 +

## hacking earc/component-di

You can register your own component resolver as argument to `ComponentDi::init()`.
After that `di_comp` returns the new component resolver. Just extend the existing 
`ComponentResolver::class` or implement the `ComponentResolverInterface` to bend the
logic to your need.

## releases

### release v1.0

complete rewrite based on the complete rewrite of [earc/di (release v2.0)](https://github.com/Koudela/eArc-di#release-v20-alpha)

### release v0.0

initial release
