# phpstan-rules

[![Integrate](https://github.com/ergebnis/phpstan-rules/workflows/Integrate/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions)
[![Merge](https://github.com/ergebnis/phpstan-rules/workflows/Merge/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions)
[![Release](https://github.com/ergebnis/phpstan-rules/workflows/Release/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions)
[![Renew](https://github.com/ergebnis/phpstan-rules/workflows/Renew/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions)

[![Code Coverage](https://codecov.io/gh/ergebnis/phpstan-rules/branch/main/graph/badge.svg)](https://codecov.io/gh/ergebnis/phpstan-rules)

[![Latest Stable Version](https://poser.pugx.org/ergebnis/phpstan-rules/v/stable)](https://packagist.org/packages/ergebnis/phpstan-rules)
[![Total Downloads](https://poser.pugx.org/ergebnis/phpstan-rules/downloads)](https://packagist.org/packages/ergebnis/phpstan-rules)
[![Monthly Downloads](http://poser.pugx.org/ergebnis/phpstan-rules/d/monthly)](https://packagist.org/packages/ergebnis/phpstan-rules)

This project provides a [`composer`](https://getcomposer.org) package with rules for [`phpstan/phpstan`](https://github.com/phpstan/phpstan).

## Installation

Run

```sh
composer require --dev ergebnis/phpstan-rules
```

## Usage

All of the [rules](https://github.com/ergebnis/phpstan-rules#rules) provided (and used) by this library are included in [`rules.neon`](rules.neon).

When you are using [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer), `rules.neon` will be automatically included.

Otherwise you need to include `rules.neon` in your `phpstan.neon`:

```neon
includes:
	- vendor/ergebnis/phpstan-rules/rules.neon
```

:bulb: You probably want to use these rules on top of the rules provided by:

- [`phpstan/phpstan`](https://github.com/phpstan/phpstan)
- [`phpstan/phpstan-deprecation-rules`](https://github.com/phpstan/phpstan-deprecation-rules)
- [`phpstan/phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules)

## Rules

This package provides the following rules for use with [`phpstan/phpstan`](https://github.com/phpstan/phpstan):

- [`Ergebnis\PHPStan\Rules\CallLikes\NoNamedArgumentRule`](https://github.com/ergebnis/phpstan-rules#calllikesnonamedargumentrule)
- [`Ergebnis\PHPStan\Rules\Classes\FinalRule`](https://github.com/ergebnis/phpstan-rules#classesfinalrule)
- [`Ergebnis\PHPStan\Rules\Classes\NoExtendsRule`](https://github.com/ergebnis/phpstan-rules#classesnoextendsrule)
- [`Ergebnis\PHPStan\Rules\Classes\PHPUnit\Framework\TestCaseWithSuffixRule`](https://github.com/ergebnis/phpstan-rules#classesphpunitframeworktestcasewithsuffixrule)
- [`Ergebnis\PHPStan\Rules\Closures\NoNullableReturnTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#closuresnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Closures\NoParameterPassedByReferenceRule`](https://github.com/ergebnis/phpstan-rules#closuresnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Closures\NoParameterWithNullableTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#closuresnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Closures\NoParameterWithNullDefaultValueRule`](https://github.com/ergebnis/phpstan-rules#closuresnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoAssignByReferenceRule`](https://github.com/ergebnis/phpstan-rules#expressionsnoassignbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoCompactRule`](https://github.com/ergebnis/phpstan-rules#expressionsnocompactrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoErrorSuppressionRule`](https://github.com/ergebnis/phpstan-rules#expressionsnoerrorsuppressionrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoEvalRule`](https://github.com/ergebnis/phpstan-rules#expressionsnoevalrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoIssetRule`](https://github.com/ergebnis/phpstan-rules#expressionsnoissetrule)
- [`Ergebnis\PHPStan\Rules\Files\DeclareStrictTypesRule`](https://github.com/ergebnis/phpstan-rules#filesdeclarestricttypesrule)
- [`Ergebnis\PHPStan\Rules\Functions\NoNullableReturnTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#functionsnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Functions\NoParameterPassedByReferenceRule`](https://github.com/ergebnis/phpstan-rules#functionsnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Functions\NoParameterWithNullableTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#functionsnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Functions\NoParameterWithNullDefaultValueRule`](https://github.com/ergebnis/phpstan-rules#functionsnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Functions\NoReturnByReferenceRule`](https://github.com/ergebnis/phpstan-rules#functionsnoreturnbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Methods\FinalInAbstractClassRule`](https://github.com/ergebnis/phpstan-rules#methodsfinalinabstractclassrule)
- [`Ergebnis\PHPStan\Rules\Methods\InvokeParentHookMethodRule`](https://github.com/ergebnis/phpstan-rules#methodsinvokeparenthookmethodrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoConstructorParameterWithDefaultValueRule`](https://github.com/ergebnis/phpstan-rules#methodsnoconstructorparameterwithdefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Methods\NoNullableReturnTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#methodsnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterPassedByReferenceRule`](https://github.com/ergebnis/phpstan-rules#methodsnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterWithContainerTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#methodsnoparameterwithcontainertypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterWithNullableTypeDeclarationRule`](https://github.com/ergebnis/phpstan-rules#methodsnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterWithNullDefaultValueRule`](https://github.com/ergebnis/phpstan-rules#methodsnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Methods\NoReturnByReferenceRule`](https://github.com/ergebnis/phpstan-rules#methodsnoreturnbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Methods\PrivateInFinalClassRule`](https://github.com/ergebnis/phpstan-rules#methodsprivateinfinalclassrule)
- [`Ergebnis\PHPStan\Rules\Statements\NoSwitchRule`](https://github.com/ergebnis/phpstan-rules#statementsnoswitchrule)


### CallLikes

#### `CallLikes\NoNamedArgumentRule`

This rule reports an error when an anonymous function, a function, or a method is invoked using a [named argument](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noNamedArgument:
			enabled: false
```

### Classes

#### `Classes\FinalRule`

This rule reports an error when a non-anonymous class is not `final`.

:bulb: This rule ignores classes that

- use `@Entity`, `@ORM\Entity`, or `@ORM\Mapping\Entity` annotations
- use `Doctrine\ORM\Mapping\Entity` attributes

on the class level.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		final:
			enabled: false
```

##### Disallowing `abstract` classes

By default, this rule allows to declare `abstract` classes.

You can set the `allowAbstractClasses` parameter to `false` to disallow abstract classes.

```neon
parameters:
	ergebnis:
		final:
			allowAbstractClasses: false
```

##### Excluding classes from inspection

You can set the `classesNotRequiredToBeAbstractOrFinal` parameter to a list of class names that you want to exclude from inspection.

```neon
parameters:
	ergebnis:
		final:
			classesNotRequiredToBeAbstractOrFinal:
				- Foo\Bar\NeitherAbstractNorFinal
				- Bar\Baz\NeitherAbstractNorFinal
```

#### `Classes\NoExtendsRule`

This rule reports an error when a class extends another class.

##### Defaults

By default, this rule allows the following classes to be extended:

- [`PHPUnit\Framework\TestCase`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php)

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noExtends:
			enabled: false
```

##### Allowing classes to be extended

You can set the `classesAllowedToBeExtended` parameter to a list of class names that you want to allow to be extended.

```neon
parameters:
	ergebnis:
		noExtends:
			classesAllowedToBeExtended:
				- Ergebnis\PHPStan\Rules\Test\Integration\AbstractTestCase
				- Ergebnis\PHPStan\Rules\Test\Integration\AbstractTestCase
```

#### `Classes\PHPUnit\Framework\TestCaseWithSuffixRule`

This rule reports an error when a concrete class is a sub-class of `PHPUnit\Framework\TestCase` but does not have a `Test` suffix.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		testCaseWithSuffix:
			enabled: false
```

### Closures

#### `Closures\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a closure uses a nullable return type declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noNullableReturnTypeDeclaration:
			enabled: false
```

#### `Closures\NoParameterPassedByReferenceRule`

This rule reports an error when a closure has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterPassedByReference:
			enabled: false
```

#### `Closures\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a closure has a parameter with a nullable type declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullableTypeDeclaration:
			enabled: false
```

#### `Closures\NoParameterWithNullDefaultValueRule`

This rule reports an error when a closure has a parameter with `null` as default value.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullDefaultValue:
			enabled: false
```

### Expressions

#### `Expressions\NoAssignByReferenceRule`

This rule reports an error when [a variable is assigned by reference](https://www.php.net/manual/en/language.references.whatdo.php#language.references.whatdo.assign).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noAssignByReference:
			enabled: false
```

#### `Expressions\NoCompactRule`

This rule reports an error when the function [`compact()`](https://www.php.net/compact) is used.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noCompact:
			enabled: false
```

#### `Expressions\NoErrorSuppressionRule`

This rule reports an error when [`@`](https://www.php.net/manual/en/language.operators.errorcontrol.php) is used to suppress errors.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noErrorSuppression:
			enabled: false
```

#### `Expressions\NoEvalRule`

This rule reports an error when the language construct [`eval()`](https://www.php.net/eval) is used.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noEval:
			enabled: false
```

#### `Expressions\NoIssetRule`

This rule reports an error when the language construct [`isset()`](https://www.php.net/isset) is used.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noIsset:
			enabled: false
```

### Files

#### `Files\DeclareStrictTypesRule`

This rule reports an error when a non-empty file does not contain a `declare(strict_types=1)` declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		declareStrictTypes:
			enabled: false
```

### Functions

#### `Functions\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a function uses a nullable return type declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noNullableReturnTypeDeclaration:
			enabled: false
```

#### `Functions\NoParameterPassedByReferenceRule`

This rule reports an error when a function has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterPassedByReference:
			enabled: false
```

#### `Functions\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a function has a parameter with a nullable type declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullableTypeDeclaration:
			enabled: false
```

#### `Functions\NoParameterWithNullDefaultValueRule`

This rule reports an error when a function has a parameter with `null` as default value.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullDefaultValue:
			enabled: false
```

#### `Functions\NoReturnByReferenceRule`

This rule reports an error when a function [returns by reference](https://www.php.net/manual/en/language.references.return.php).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noReturnByReference:
			enabled: false
```

### Methods

#### `Methods\FinalInAbstractClassRule`

This rule reports an error when a concrete `public` or `protected` method in an `abstract` class is not `final`.

:bulb: This rule ignores

- Doctrine embeddables
- Doctrine entities

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		finalInAbstractClass:
			enabled: false
```

#### `Methods\InvokeParentHookMethodRule`

This rule reports an error when a hook method that overrides a hook method in a parent class does not invoke the overridden hook method in the expected order.

##### Defaults

By default, this rule requires the following hook methods to be invoked before doing something in the overriding method:

- [`Codeception\PHPUnit\TestCase::_setUp()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L11-L13)
- [`Codeception\PHPUnit\TestCase::_setUpBeforeClass()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L25-L27)
- [`Codeception\Test\Unit::_before()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L63-L65)
- [`Codeception\Test\Unit::_setUp()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L34-L58)
- [`PHPUnit\Framework\TestCase::assertPreConditions()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2073-L2075)
- [`PHPUnit\Framework\TestCase::setUp()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2063-L2065)
- [`PHPUnit\Framework\TestCase::setUpBeforeClass()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2055-L2057)

By default, this rule requires the following hook methods to be invoked after doing something in the overriding method:

- [`Codeception\PHPUnit\TestCase::_tearDown()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L18-L20)
- [`Codeception\PHPUnit\TestCase::_tearDownAfterClass()`](https://github.com/Codeception/phpunit-wrapper/blob/9.0.0/src/TestCase.php#L32-L34)
- [`Codeception\Test\Unit::_after()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L75-L77)
- [`Codeception\Test\Unit::_tearDown()`](https://github.com/Codeception/Codeception/blob/4.2.2/src/Codeception/Test/Unit.php#L67-L70)
- [`PHPUnit\Framework\TestCase::assertPostConditions()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2083-L2085)
- [`PHPUnit\Framework\TestCase::tearDown()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2091-L2093)
- [`PHPUnit\Framework\TestCase::tearDownAfterClass()`](https://github.com/sebastianbergmann/phpunit/blob/6.0.0/src/Framework/TestCase.php#L2098-L2100)

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		invokeParentHookMethod:
			enabled: false
```

##### Configuring methods to invoke the parent method in the right order:

You can set the `hookMethods` parameter to a list of hook methods:

```neon
parameters:
	ergebnis:
		invokeParentHookMethod:
			hookMethods:
				- className: "Example\Test\Functional\AbstractCest"
					methodName: "_before"
					hasContent: "yes"
					invocation: "first"
```

- `className`: name of the class that declares the hook method
- `methodName`: name of the hook method
- `hasContent`: one of `"yes"`, `"no"`, `"maybe"`
- `invocation`: one of `"any"` (needs to be invoked), `"first"` (needs to be invoked before all other statements in the overriding hook method, `"last"` (needs to be invoked after all other statements in the overriding hook method)

#### `Methods\NoConstructorParameterWithDefaultValueRule`

This rule reports an error when a constructor declared in

- an anonymous class
- a class

has a default value.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noConstructorParameterWithDefaultValue:
			enabled: false
```

#### `Methods\NoParameterPassedByReferenceRule`

This rule reports an error when a method has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterPassedByReference:
			enabled: false
```

#### `Methods\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a method declared in

- an anonymous class
- a class
- an interface

uses a nullable return type declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noNullableReturnTypeDeclaration:
			enabled: false
```

#### `Methods\NoParameterWithContainerTypeDeclarationRule`

This rule reports an error when a method has a type declaration for a known dependency injection container or service locator.

##### Defaults

By default, this rule disallows the use of type declarations indicating an implementation of

- [`Psr\Container\ContainerInterface`](https://github.com/php-fig/container/blob/1.0.0/src/ContainerInterface.php)

is expected to be injected into a method.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithContainerTypeDeclaration:
			enabled: false
```

##### Configuring container interfaces

You can set the `interfacesImplementedByContainers` parameter to a list of interface names of additional containers and service locators.

```neon
parameters:
	ergebnis:
		noParameterWithContainerTypeDeclaration:
			interfacesImplementedByContainers:
				- Fancy\DependencyInjection\ContainerInterface
				- Other\ServiceLocatorInterface
```

##### Configuring methods allowed to use parameters with container type declarations

You can set the `methodsAllowedToUseContainerTypeDeclarations` parameter to a list of method names that are allowed to use parameters with container type declarations.

```neon
parameters:
	ergebnis:
		noParameterWithContainerTypeDeclaration:
			methodsAllowedToUseContainerTypeDeclarations:
				- loadExtension
```

#### `Methods\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a method declared in

- an anonymous class
- a class
- an interface

has a parameter with a nullable type declaration.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullableTypeDeclaration:
			enabled: false
```

#### `Methods\NoParameterWithNullDefaultValueRule`

This rule reports an error when a method declared in

- an anonymous class
- a class
- an interface

has a parameter with `null` as default value.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noParameterWithNullDefaultValue:
			enabled: false
```

#### `Functions\NoReturnByReferenceRule`

This rule reports an error when a method [returns by reference](https://www.php.net/manual/en/language.references.return.php).

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noReturnByReference:
			enabled: false
```

#### `Methods\PrivateInFinalClassRule`

This rule reports an error when a method in a `final` class is `protected` but could be `private`.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		privateInFinalClass:
			enabled: false
```

### Statements

#### `Statements\NoSwitchRule`

This rule reports an error when the statement [`switch()`](https://www.php.net/manual/control-structures.switch.php) is used.

##### Disabling the rule

You can set the `enabled` parameter to `false` to disable this rule.

```neon
parameters:
	ergebnis:
		noSwitch:
			enabled: false
```

## Disabling all rules

You can disable all rules using the `allRules` configuration parameter:

```neon
parameters:
	ergebnis:
		allRules: false
```

## Enabling rules one-by-one

If you have disabled all rules using the `allRules` configuration parameter, you can re-enable individual rules with their corresponding configuration parameters:

```neon
parameters:
	ergebnis:
		allRules: false
		privateInFinalClass:
			enabled: true
```

## Changelog

The maintainers of this project record notable changes to this project in a [changelog](CHANGELOG.md).

## Contributing

The maintainers of this project suggest following the [contribution guide](.github/CONTRIBUTING.md).

## Code of Conduct

The maintainers of this project ask contributors to follow the [code of conduct](https://github.com/ergebnis/.github/blob/main/CODE_OF_CONDUCT.md).

## General Support Policy

The maintainers of this project provide limited support.

You can support the maintenance of this project by [sponsoring @ergebnis](https://github.com/sponsors/ergebnis).

## PHP Version Support Policy

This project supports PHP versions with [active and security support](https://www.php.net/supported-versions.php).

The maintainers of this project add support for a PHP version following its initial release and drop support for a PHP version when it has reached the end of security support.

## Security Policy

This project has a [security policy](.github/SECURITY.md).

## License

This project uses the [MIT license](LICENSE.md).

## Credits

The method [`FinalRule::isWhitelistedClass()`](src/Classes/FinalRule.php) is inspired by the work on [`FinalClassFixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.15/src/Fixer/ClassNotation/FinalClassFixer.php) and [`FinalInternalClassFixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.15/src/Fixer/ClassNotation/FinalInternalClassFixer.php), contributed by [Dariusz Rumiński](https://github.com/keradus), [Filippo Tessarotto](https://github.com/Slamdunk), and [Spacepossum](https://github.com/SpacePossum) for [`friendsofphp/php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) (originally licensed under MIT).

## Social

Follow [@localheinz](https://twitter.com/intent/follow?screen_name=localheinz) and [@ergebnis](https://twitter.com/intent/follow?screen_name=ergebnis) on Twitter.
