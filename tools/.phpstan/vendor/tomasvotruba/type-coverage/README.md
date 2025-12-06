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
