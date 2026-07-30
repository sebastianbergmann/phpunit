# Require Minimal Type Coverage

<br>

A PHPStan extension, to check and require minimal type coverage of PHP code.

<br>

The type coverage rate = total count of **defined** type declarations / total count of **possible** type declarations.

E.g. we have 10 methods, but only 7 have defined return type = 70 % return type coverage.

---

PHPStan uses type declarations to determine the type of variables, properties and other expression. Sometimes it's hard to see what PHPStan errors are the important ones among thousands of others.

Instead of fixing all PHPStan errors at once, we can start with minimal require type coverage.

<br>


## How to increase type coverage?

Here we have 3 possible type declarations:

* property,
* param
* and return type

```php
final class ConferenceFactory
{
    private $talkFactory;

    public function createConference(array $data)
    {
        $talks = $this->talkFactory->create($data);

        return new Conference($talks);
    }
}
```

The param type is defined as `array`.

1 defined / 3 possible = **33.3 % type coverage**

<br>

Our code quality is only at one-third of its potential. Let's get to 100 %!

```diff
 final class ConferenceFactory
 {
-    private $talkFactory;
+    private TalkFactory $talkFactory;

-    public function createConference(array $data)
+    public function createConference(array $data): Conference
     {
         $talks = $this->talkFactory->create($data);

         return new Conference($talks);
     }
 }
```

This technique is very simple to start even on legacy project. Also, you're now aware exactly how high coverage your project has.

<br>

## Install

```bash
composer require tomasvotruba/type-coverage --dev
```

The package is available on PHP 7.2+.

<br>

## Usage

With [PHPStan extension installer](https://github.com/phpstan/extension-installer), everything is ready to run.

Enable each item on their own:

```yaml
# phpstan.neon
parameters:
    type_coverage:
        return: 50
        param: 35.5
        property: 70

        # since PHP 8.3
        constant: 85
```

<br>

## Measure Strict Declares coverage

Once you've reached 100 % type coverage, make sure [your code is strict and uses types](https://tomasvotruba.com/blog/how-adding-type-declarations-makes-your-code-dangerous):

```php
<?php

declare(strict_types=1);
```

Again, raise level percent by percent in your own pace:

```yaml
parameters:
    type_coverage:
        declare: 40
```

<br>

## Full Paths only

If you run PHPStan only on some subpaths that are different from your setup in `phpstan.neon`, e.g.:

```bash
vendor/bin/phpstan analyze src/Controller
```

This package could show false positives, as classes in the `src/Controller` could be slightly less typed. This would be spamming whole PHPStan output and make hard to see any other errors you look for.

That's why this package only triggers if there are full paths, e.g.:

```bash
vendor/bin/phpstan
````

<br>

Happy coding!

<br>

---

# Type Perfect

[![Downloads](https://img.shields.io/packagist/dt/rector/type-perfect.svg?style=flat-square)](https://packagist.org/packages/rector/type-perfect/stats)

Next level type declaration check PHPStan rules.

We use these sets to improve code quality of our clients' code beyond PHPStan features.

* These rules make skipped object types explicit, param types narrow and help you to fill more accurate object type hints.
* **They're easy to enable, even if your code does not pass level 0**
* They're effortless to resolve and make your code instantly more solid and reliable.

If you care about code quality and type safety, add these 10 rules to your CI.

<br>

These rules ship with the package [installed above](#install), there is nothing else to require.

*Migrating from `rector/type-perfect`? Remove it from your `composer.json`, the rules live here now.*

<br>

Every rule is opt-in and disabled by default, so you can pick the ones that fit your project. See [Configure](#configure) below for the full list.

The 3 checks below are the simplest ones to start with:

```yaml
parameters:
    type_perfect:
        no_isset_on_object: true
        no_empty_on_object: true
        no_array_access_on_object: true
        no_param_type_removal: true
```

The first one makes sure we don't miss a chance to use `instanceof` to make further code know about exact object type:

```php
private ?SomeType $someType = null;

if (! empty($this->someType)) {
    // ...
}

if (! isset($this->someType)) {
    // ...
}

// here we only know, that $this->someType is not empty/null
```

:no_good:

↓


```php
if (! $this->someType instanceof SomeType) {
    return;
}

// here we know $this->someType is exactly SomeType
```

:heavy_check_mark:

<br>

Second rule (`no_array_access_on_object`) checks we use explicit object methods over magic array access:

```php
$article = new Article();

$id = $article['id'];
// we have no idea, what the type is
```

:no_good:

↓

```php
$id = $article->getId();
// we know the type is int
```

:heavy_check_mark:

<br>

Last rule (`no_param_type_removal`) checks that all interface implementations follow the same method signature as the interface:

```php
interface SomeInterface
{
    public function doSomething(int $value): void;
}

final class SomeClass implements SomeInterface
{
     public function doSomething($value): void { // ... }
}
```

:no_good:

↓

```php
final class SomeClass implements SomeInterface
{
     public function doSomething(int $value): void { // ... }
}
```

:heavy_check_mark:

<br>

## Configure

All rules are enabled by configuration and disabled by default. We take them from the simplest to more powerful, in the same order we apply them on legacy projects.

You can enable them all at once:

```yaml
parameters:
    type_perfect:
        # the 3 checks above
        no_isset_on_object: true
        no_empty_on_object: true
        no_array_access_on_object: true
        no_param_type_removal: true

        no_mixed_property: true
        no_mixed_caller: true
        null_over_false: true
        narrow_param: true
        narrow_return: true
```

Or one by one:

<br>

## 1. Null over False

```yaml
parameters:
    type_perfect:
        null_over_false: true
```

Bool types are typically used for on/off, yes/no responses. But sometimes, the `false` is misused as *no-result* response, where `null` would be more accurate:

```php
public function getProduct()
{
    if (...) {
        return $product;
    }

    return false;
}
```

:no_good:

↓

We should use `null` instead, as it enabled strict type declaration in form of `?Product` since PHP 7.1:

```php
public function getProduct(): ?Product
{
    if (...) {
        return $product;
    }

    return null;
}
```

:heavy_check_mark:

<br>

## 2. No mixed Property

```yaml
parameters:
    type_perfect:
        no_mixed_property: true
```

This rule focuses on PHPStan blind spot while fetching a property. If we have a property with unknown type, PHPStan is not be able to analyse it. It silently ignores it.

```php
private $someType;

public function run()
{
    $this->someType->vale;
}
```

It doesn't see there is a typo in `vale` property name. It should be `value`

:no_good:

↓


```php
private SomeType $someType;

public function run()
{
    $this->someType->value;
}
```

This rule makes sure all property fetches know their type they're called on.

:heavy_check_mark:

<br>

## 3. No mixed Caller

```yaml
parameters:
    type_perfect:
        no_mixed_caller: true
```

Same as above, only for method calls:

```php
private $someType;

public function run()
{
    $this->someType->someMetho(1, 2);
}
```

It doesn't see there is a typo in `someMetho` name, and that the 2nd parameter must be `string`.

:no_good:

↓


```php
private SomeType $someType;

public function run()
{
    $this->someType->someMethod(1, 'active');
}
```

This group makes sure methods call know their type they're called on.

:heavy_check_mark:

<br>

## 4. Narrow Param Types

The more narrow param type we have, the reliable the code is. `string` beats `mixed`, `int` beats `scalar` and `ExactObject` beats `stdClass`.

```yaml
parameters:
    type_perfect:
        narrow_param: true
```

In case of `private`, but also `public` method calls, our project often knows exact types that are passed in it:

```php
// in one file
$product->addPrice(100.52);

// another file
$product->addPrice(52.05);
```

But out of from fear and "just to be safe", we keep the `addPrice()` param type empty, `mixed` or in a docblock.

:no_good:

↓

If, in 100 % cases the `float` type is passed, PHPStan knows it can be added and improve further analysis:

```diff

-/**
- * @param float $price
- */
-public function addPrice($price)
+public function addPrice(float $price)
{
    $this->price = $price;
}
```

That's where this group comes in. It checks all the passed types, and tells us know how to narrow the param type declaration.

:heavy_check_mark:

<br>

## 5. Narrow Return Types

Last but not least, the more narrow return type, the more reliable the code.

```yaml
parameters:
    type_perfect:
        narrow_return: true
```

Where does it help? Let's say we have 2 types of talks, that do have different behavior:

```php
final class ConferenceTalk extends Talk
{
    public function bookHotel()
    {
        // ...
    }
}

final class MeetupTalk extends Talk
{
    public function bookTrain()
    {
        // ...
    }
}
```

Then we have a factory (repository, or services) that returns generic `Talk` type:

```php
final class TalkFactory
{
    public function createConferenceTalk(): Talk
    {
        return new ConferenceTalk();
    }

    public function createMeetupTalk(): Talk
    {
        return new MeetupTalk();
    }
}
```

In this case we've just lost strict type and have to verify the type on runtime:

```php
$talk instanceof ConferenceTalk
```

:no_good:

↓

That's where this group comes in. In case we return the exact type, we should use exact type in return type declaration to keep the code as reliable as possible:

```diff
 final class TalkFactory
 {
-    public function createConferenceTalk(): Talk
+    public function createConferenceTalk(): ConferenceTalk
     {
         return new ConferenceTalk();
     }

-    public function createMeetupTalk(): Talk
+    public function createMeetupTalk(): MeetupTalk
     {
         return new MeetupTalk();
     }
}
```

:heavy_check_mark:

Add sets one by one, fix what you find helpful and ignore the rest.

<br>

Happy coding!
