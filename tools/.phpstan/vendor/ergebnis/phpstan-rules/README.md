# phpstan-rules

[![Integrate](https://github.com/ergebnis/phpstan-rules/actions/workflows/integrate.yaml/badge.svg?branch=main)](https://github.com/ergebnis/phpstan-rules/actions/workflows/integrate.yaml)
[![Merge](https://github.com/ergebnis/phpstan-rules/actions/workflows/merge.yaml/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions/workflows/merge.yaml)
[![Release](https://github.com/ergebnis/phpstan-rules/actions/workflows/release.yaml/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions/workflows/release.yaml)
[![Renew](https://github.com/ergebnis/phpstan-rules/actions/workflows/renew.yaml/badge.svg)](https://github.com/ergebnis/phpstan-rules/actions/workflows/renew.yaml)

[![Code Coverage](https://codecov.io/gh/ergebnis/phpstan-rules/branch/main/graph/badge.svg)](https://codecov.io/gh/ergebnis/phpstan-rules)

[![Latest Stable Version](https://poser.pugx.org/ergebnis/phpstan-rules/v/stable)](https://packagist.org/packages/ergebnis/phpstan-rules)
[![Total Downloads](https://poser.pugx.org/ergebnis/phpstan-rules/downloads)](https://packagist.org/packages/ergebnis/phpstan-rules)
[![Monthly Downloads](https://poser.pugx.org/ergebnis/phpstan-rules/d/monthly)](https://packagist.org/packages/ergebnis/phpstan-rules)

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

💡 You probably want to use these rules on top of the rules provided by:

- [`phpstan/phpstan`](https://github.com/phpstan/phpstan)
- [`phpstan/phpstan-deprecation-rules`](https://github.com/phpstan/phpstan-deprecation-rules)
- [`phpstan/phpstan-strict-rules`](https://github.com/phpstan/phpstan-strict-rules)

## Rules

<!-- BEGIN RULES -->

This package provides the following rules for use with [`phpstan/phpstan`](https://github.com/phpstan/phpstan):

- [`Ergebnis\PHPStan\Rules\ArrowFunctions\NoNullableReturnTypeDeclarationRule`](#arrowfunctionsnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\ArrowFunctions\NoParameterPassedByReferenceRule`](#arrowfunctionsnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\ArrowFunctions\NoParameterWithNullDefaultValueRule`](#arrowfunctionsnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\ArrowFunctions\NoParameterWithNullableTypeDeclarationRule`](#arrowfunctionsnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\CallLikes\NoNamedArgumentRule`](#calllikesnonamedargumentrule)
- [`Ergebnis\PHPStan\Rules\Classes\FinalRule`](#classesfinalrule)
- [`Ergebnis\PHPStan\Rules\Classes\NoExtendsRule`](#classesnoextendsrule)
- [`Ergebnis\PHPStan\Rules\Classes\PHPUnit\Framework\TestCaseWithSuffixRule`](#classesphpunitframeworktestcasewithsuffixrule)
- [`Ergebnis\PHPStan\Rules\Closures\NoNullableReturnTypeDeclarationRule`](#closuresnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Closures\NoParameterPassedByReferenceRule`](#closuresnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Closures\NoParameterWithNullDefaultValueRule`](#closuresnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Closures\NoParameterWithNullableTypeDeclarationRule`](#closuresnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoAssignByReferenceRule`](#expressionsnoassignbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoCompactRule`](#expressionsnocompactrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoErrorSuppressionRule`](#expressionsnoerrorsuppressionrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoEvalRule`](#expressionsnoevalrule)
- [`Ergebnis\PHPStan\Rules\Expressions\NoIssetRule`](#expressionsnoissetrule)
- [`Ergebnis\PHPStan\Rules\Files\DeclareStrictTypesRule`](#filesdeclarestricttypesrule)
- [`Ergebnis\PHPStan\Rules\Files\NoPhpstanIgnoreRule`](#filesnophpstanignorerule)
- [`Ergebnis\PHPStan\Rules\Functions\NoNullableReturnTypeDeclarationRule`](#functionsnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Functions\NoParameterPassedByReferenceRule`](#functionsnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Functions\NoParameterWithNullDefaultValueRule`](#functionsnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Functions\NoParameterWithNullableTypeDeclarationRule`](#functionsnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Functions\NoReturnByReferenceRule`](#functionsnoreturnbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Methods\FinalInAbstractClassRule`](#methodsfinalinabstractclassrule)
- [`Ergebnis\PHPStan\Rules\Methods\InvokeParentHookMethodRule`](#methodsinvokeparenthookmethodrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoConstructorParameterWithDefaultValueRule`](#methodsnoconstructorparameterwithdefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Methods\NoNullableReturnTypeDeclarationRule`](#methodsnonullablereturntypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterPassedByReferenceRule`](#methodsnoparameterpassedbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterWithContainerTypeDeclarationRule`](#methodsnoparameterwithcontainertypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterWithNullDefaultValueRule`](#methodsnoparameterwithnulldefaultvaluerule)
- [`Ergebnis\PHPStan\Rules\Methods\NoParameterWithNullableTypeDeclarationRule`](#methodsnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\Methods\NoReturnByReferenceRule`](#methodsnoreturnbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Methods\PrivateInFinalClassRule`](#methodsprivateinfinalclassrule)
- [`Ergebnis\PHPStan\Rules\PropertyHooks\NoParameterWithNullableTypeDeclarationRule`](#propertyhooksnoparameterwithnullabletypedeclarationrule)
- [`Ergebnis\PHPStan\Rules\PropertyHooks\NoReturnByReferenceRule`](#propertyhooksnoreturnbyreferencerule)
- [`Ergebnis\PHPStan\Rules\Statements\NoSwitchRule`](#statementsnoswitchrule)

### ArrowFunctions

#### `ArrowFunctions\NoNullableReturnTypeDeclarationRule`

This rule reports an error when an arrow function uses a nullable return type declaration.

💡 Find out more in the rule documentation for [`ArrowFunctions\NoNullableReturnTypeDeclarationRule`](doc/rules/ArrowFunctions/NoNullableReturnTypeDeclarationRule.md).

#### `ArrowFunctions\NoParameterPassedByReferenceRule`

This rule reports an error when an arrow function has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

💡 Find out more in the rule documentation for [`ArrowFunctions\NoParameterPassedByReferenceRule`](doc/rules/ArrowFunctions/NoParameterPassedByReferenceRule.md).

#### `ArrowFunctions\NoParameterWithNullDefaultValueRule`

This rule reports an error when an arrow function has a parameter with `null` as default value.

💡 Find out more in the rule documentation for [`ArrowFunctions\NoParameterWithNullDefaultValueRule`](doc/rules/ArrowFunctions/NoParameterWithNullDefaultValueRule.md).

#### `ArrowFunctions\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when an arrow function has a parameter with a nullable type declaration.

💡 Find out more in the rule documentation for [`ArrowFunctions\NoParameterWithNullableTypeDeclarationRule`](doc/rules/ArrowFunctions/NoParameterWithNullableTypeDeclarationRule.md).

### CallLikes

#### `CallLikes\NoNamedArgumentRule`

This rule reports an error when an anonymous function, a function, or a method is invoked using a [named argument](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments).

💡 Find out more in the rule documentation for [`CallLikes\NoNamedArgumentRule`](doc/rules/CallLikes/NoNamedArgumentRule.md).

### Classes

#### `Classes\FinalRule`

This rule reports an error when a non-anonymous class is not `final`.

💡 By default, this rule ignores classes that

- use `@Entity`, `@ORM\Entity`, or `@ORM\Mapping\Entity` annotations
- use `Doctrine\ORM\Mapping\Entity` attributes

on the class level.

💡 Find out more in the rule documentation for [`Classes\FinalRule`](doc/rules/Classes/FinalRule.md).

#### `Classes\NoExtendsRule`

This rule reports an error when a class extends another class.

💡 Find out more in the rule documentation for [`Classes\NoExtendsRule`](doc/rules/Classes/NoExtendsRule.md).

#### `Classes\PHPUnit\Framework\TestCaseWithSuffixRule`

This rule reports an error when a concrete class is a sub-class of `PHPUnit\Framework\TestCase` but does not have a `Test` suffix.

💡 Find out more in the rule documentation for [`Classes\PHPUnit\Framework\TestCaseWithSuffixRule`](doc/rules/Classes/PHPUnit/Framework/TestCaseWithSuffixRule.md).

### Closures

#### `Closures\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a closure uses a nullable return type declaration.

💡 Find out more in the rule documentation for [`Closures\NoNullableReturnTypeDeclarationRule`](doc/rules/Closures/NoNullableReturnTypeDeclarationRule.md).

#### `Closures\NoParameterPassedByReferenceRule`

This rule reports an error when a closure has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

💡 Find out more in the rule documentation for [`Closures\NoParameterPassedByReferenceRule`](doc/rules/Closures/NoParameterPassedByReferenceRule.md).

#### `Closures\NoParameterWithNullDefaultValueRule`

This rule reports an error when a closure has a parameter with `null` as default value.

💡 Find out more in the rule documentation for [`Closures\NoParameterWithNullDefaultValueRule`](doc/rules/Closures/NoParameterWithNullDefaultValueRule.md).

#### `Closures\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a closure has a parameter with a nullable type declaration.

💡 Find out more in the rule documentation for [`Closures\NoParameterWithNullableTypeDeclarationRule`](doc/rules/Closures/NoParameterWithNullableTypeDeclarationRule.md).

### Expressions

#### `Expressions\NoAssignByReferenceRule`

This rule reports an error when [a variable is assigned by reference](https://www.php.net/manual/en/language.references.whatdo.php#language.references.whatdo.assign).

💡 Find out more in the rule documentation for [`Expressions\NoAssignByReferenceRule`](doc/rules/Expressions/NoAssignByReferenceRule.md).

#### `Expressions\NoCompactRule`

This rule reports an error when the function [`compact()`](https://www.php.net/compact) is used.

💡 Find out more in the rule documentation for [`Expressions\NoCompactRule`](doc/rules/Expressions/NoCompactRule.md).

#### `Expressions\NoErrorSuppressionRule`

This rule reports an error when [`@`](https://www.php.net/manual/en/language.operators.errorcontrol.php) is used to suppress errors.

💡 Find out more in the rule documentation for [`Expressions\NoErrorSuppressionRule`](doc/rules/Expressions/NoErrorSuppressionRule.md).

#### `Expressions\NoEvalRule`

This rule reports an error when the language construct [`eval()`](https://www.php.net/eval) is used.

💡 Find out more in the rule documentation for [`Expressions\NoEvalRule`](doc/rules/Expressions/NoEvalRule.md).

#### `Expressions\NoIssetRule`

This rule reports an error when the language construct [`isset()`](https://www.php.net/isset) is used.

💡 Find out more in the rule documentation for [`Expressions\NoIssetRule`](doc/rules/Expressions/NoIssetRule.md).

### Files

#### `Files\DeclareStrictTypesRule`

This rule reports an error when a non-empty file does not contain a `declare(strict_types=1)` declaration.

💡 Find out more in the rule documentation for [`Files\DeclareStrictTypesRule`](doc/rules/Files/DeclareStrictTypesRule.md).

#### `Files\NoPhpstanIgnoreRule`

This rule reports an error when a `@phpstan-ignore`, `@phpstan-ignore-line`, or `@phpstan-ignore-next-line` tag is used to [ignore errors reported by `phpstan/phpstan`](https://phpstan.org/user-guide/ignoring-errors#ignoring-in-code-using-phpdocs).

💡 Find out more in the rule documentation for [`Files\NoPhpstanIgnoreRule`](doc/rules/Files/NoPhpstanIgnoreRule.md).

### Functions

#### `Functions\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a function uses a nullable return type declaration.

💡 Find out more in the rule documentation for [`Functions\NoNullableReturnTypeDeclarationRule`](doc/rules/Functions/NoNullableReturnTypeDeclarationRule.md).

#### `Functions\NoParameterPassedByReferenceRule`

This rule reports an error when a function has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

💡 Find out more in the rule documentation for [`Functions\NoParameterPassedByReferenceRule`](doc/rules/Functions/NoParameterPassedByReferenceRule.md).

#### `Functions\NoParameterWithNullDefaultValueRule`

This rule reports an error when a function has a parameter with `null` as default value.

💡 Find out more in the rule documentation for [`Functions\NoParameterWithNullDefaultValueRule`](doc/rules/Functions/NoParameterWithNullDefaultValueRule.md).

#### `Functions\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a function has a parameter with a nullable type declaration.

💡 Find out more in the rule documentation for [`Functions\NoParameterWithNullableTypeDeclarationRule`](doc/rules/Functions/NoParameterWithNullableTypeDeclarationRule.md).

#### `Functions\NoReturnByReferenceRule`

This rule reports an error when a function [returns by reference](https://www.php.net/manual/en/language.references.return.php).

💡 Find out more in the rule documentation for [`Functions\NoReturnByReferenceRule`](doc/rules/Functions/NoReturnByReferenceRule.md).

### Methods

#### `Methods\FinalInAbstractClassRule`

This rule reports an error when a concrete `public` or `protected` method in an `abstract` class is not `final`.

💡 By default, this rule ignores Doctrine entities.

💡 Find out more in the rule documentation for [`Methods\FinalInAbstractClassRule`](doc/rules/Methods/FinalInAbstractClassRule.md).

#### `Methods\InvokeParentHookMethodRule`

This rule reports an error when a hook method that overrides a hook method in a parent class does not invoke the overridden hook method in the expected order.

💡 Find out more in the rule documentation for [`Methods\InvokeParentHookMethodRule`](doc/rules/Methods/InvokeParentHookMethodRule.md).

#### `Methods\NoConstructorParameterWithDefaultValueRule`

This rule reports an error when a constructor has a parameter with a default value.

💡 Find out more in the rule documentation for [`Methods\NoConstructorParameterWithDefaultValueRule`](doc/rules/Methods/NoConstructorParameterWithDefaultValueRule.md).

#### `Methods\NoNullableReturnTypeDeclarationRule`

This rule reports an error when a method uses a nullable return type declaration.

💡 Find out more in the rule documentation for [`Methods\NoNullableReturnTypeDeclarationRule`](doc/rules/Methods/NoNullableReturnTypeDeclarationRule.md).

#### `Methods\NoParameterPassedByReferenceRule`

This rule reports an error when a method has a parameter that is [passed by reference](https://www.php.net/manual/en/language.references.pass.php).

💡 Find out more in the rule documentation for [`Methods\NoParameterPassedByReferenceRule`](doc/rules/Methods/NoParameterPassedByReferenceRule.md).

#### `Methods\NoParameterWithContainerTypeDeclarationRule`

This rule reports an error when a method has a type declaration for a known dependency injection container or service locator.

💡 Find out more in the rule documentation for [`Methods\NoParameterWithContainerTypeDeclarationRule`](doc/rules/Methods/NoParameterWithContainerTypeDeclarationRule.md).

#### `Methods\NoParameterWithNullDefaultValueRule`

This rule reports an error when a method has a parameter with `null` as default value.

💡 Find out more in the rule documentation for [`Methods\NoParameterWithNullDefaultValueRule`](doc/rules/Methods/NoParameterWithNullDefaultValueRule.md).

#### `Methods\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a method has a parameter with a nullable type declaration.

💡 Find out more in the rule documentation for [`Methods\NoParameterWithNullableTypeDeclarationRule`](doc/rules/Methods/NoParameterWithNullableTypeDeclarationRule.md).

#### `Methods\NoReturnByReferenceRule`

This rule reports an error when a method [returns by reference](https://www.php.net/manual/en/language.references.return.php).

💡 Find out more in the rule documentation for [`Methods\NoReturnByReferenceRule`](doc/rules/Methods/NoReturnByReferenceRule.md).

#### `Methods\PrivateInFinalClassRule`

This rule reports an error when a method in a `final` class is `protected` but could be `private`.

💡 Find out more in the rule documentation for [`Methods\PrivateInFinalClassRule`](doc/rules/Methods/PrivateInFinalClassRule.md).

### PropertyHooks

#### `PropertyHooks\NoParameterWithNullableTypeDeclarationRule`

This rule reports an error when a [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) has a parameter with a nullable type declaration.

💡 Find out more in the rule documentation for [`PropertyHooks\NoParameterWithNullableTypeDeclarationRule`](doc/rules/PropertyHooks/NoParameterWithNullableTypeDeclarationRule.md).

#### `PropertyHooks\NoReturnByReferenceRule`

This rule reports an error when a [property hook](https://www.php.net/manual/en/language.oop5.property-hooks.php) returns by reference.

💡 Find out more in the rule documentation for [`PropertyHooks\NoReturnByReferenceRule`](doc/rules/PropertyHooks/NoReturnByReferenceRule.md).

### Statements

#### `Statements\NoSwitchRule`

This rule reports an error when the statement [`switch()`](https://www.php.net/manual/control-structures.switch.php) is used.

💡 Find out more in the rule documentation for [`Statements\NoSwitchRule`](doc/rules/Statements/NoSwitchRule.md).

<!-- END RULES -->

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

## PHP Version Support Policy

This project currently supports the following PHP versions:

- [PHP 7.4](https://www.php.net/releases/#7.4.0) (has reached its end of life on November 28, 2022)
- [PHP 8.0](https://www.php.net/releases/#8.0.0) (has reached its end of life on November 26, 2023)
- [PHP 8.1](https://www.php.net/releases/#8.1.0) (has reached its end of life on December 31, 2025)
- [PHP 8.2](https://www.php.net/releases/#8.2.0)
- [PHP 8.3](https://www.php.net/releases/#8.3.0)
- [PHP 8.4](https://www.php.net/releases/#8.4.0)
- [PHP 8.5](https://www.php.net/releases/#8.5.0)

The maintainers of this project add support for a PHP version following its initial release and _may_ drop support for a PHP version when it has reached its [end of life](https://www.php.net/supported-versions.php).

## Security Policy

This project has a [security policy](.github/SECURITY.md).

## License

This project uses the [MIT license](LICENSE.md).

## Credits

The method [`FinalRule::isWhitelistedClass()`](src/Classes/FinalRule.php) is inspired by the work on [`FinalClassFixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.15/src/Fixer/ClassNotation/FinalClassFixer.php) and [`FinalInternalClassFixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.15/src/Fixer/ClassNotation/FinalInternalClassFixer.php), contributed by [Dariusz Rumiński](https://github.com/keradus), [Filippo Tessarotto](https://github.com/Slamdunk), and [Spacepossum](https://github.com/SpacePossum) for [`friendsofphp/php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) (originally licensed under MIT).

## Social

Follow [@localheinz](https://x.com/intent/follow?screen_name=localheinz) and [@ergebnis](https://x.com/intent/follow?screen_name=ergebnis) on X.
