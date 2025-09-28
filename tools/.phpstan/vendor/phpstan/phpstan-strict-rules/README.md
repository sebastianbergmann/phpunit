# Extra strict and opinionated rules for PHPStan

[![Build](https://github.com/phpstan/phpstan-strict-rules/workflows/Build/badge.svg)](https://github.com/phpstan/phpstan-strict-rules/actions)
[![Latest Stable Version](https://poser.pugx.org/phpstan/phpstan-strict-rules/v/stable)](https://packagist.org/packages/phpstan/phpstan-strict-rules)
[![License](https://poser.pugx.org/phpstan/phpstan-strict-rules/license)](https://packagist.org/packages/phpstan/phpstan-strict-rules)

[PHPStan](https://phpstan.org/) focuses on finding bugs in your code. But in PHP there's a lot of leeway in how stuff can be written. This repository contains additional rules that revolve around strictly and strongly typed code with no loose casting for those who want additional safety in extremely defensive programming:

| Configuration Parameters               | Rule Description                                                                                        |
|:---------------------------------------|:--------------------------------------------------------------------------------------------------------|
| `booleansInConditions`                 | Require booleans in `if`, `elseif`, ternary operator, after `!`, and on both sides of `&&` and `\|\|`.  |
| `booleansInLoopConditions`             | Require booleans in `while` and `do while` loop conditions.                                             |
| `numericOperandsInArithmeticOperators` | Require numeric operand in `+$var`, `-$var`, `$var++`, `$var--`, `++$var` and `--$var`. |
| `numericOperandsInArithmeticOperators` | Require numeric operand in `$var++`, `$var--`, `++$var`and `--$var`.                                    |
| `strictFunctionCalls`                  | These functions contain a `$strict` parameter for better type safety, it must be set to `true`:<br>* `in_array` (3rd parameter)<br>* `array_search` (3rd parameter)<br>* `array_keys` (3rd parameter; only if the 2nd parameter `$search_value` is provided)<br>* `base64_decode` (2nd parameter). |
| `overwriteVariablesWithLoop`           | * Disallow overwriting variables with `foreach` key and value variables.<br>* Disallow overwriting variables with `for` loop initial assignment.                                                                   |
| `switchConditionsMatchingType`         | Types in `switch` condition and `case` value must match. PHP compares them loosely by default and that can lead to  unexpected results. |
| `dynamicCallOnStaticMethod`            | Check that statically declared methods are called statically.                                           |
| `disallowedEmpty`                      | Disallow `empty()` - it's a very loose comparison (see [manual](https://php.net/empty)), it's recommended to use more  strict one. |
| `disallowedShortTernary`               | Disallow short ternary operator (`?:`) - implies weak comparison, it's recommended to use null coalesce operator (`??`)  or ternary operator with strict condition. |
| `noVariableVariables`                  | Disallow variable variables (`$$foo`, `$this->$method()` etc.).                                         |
| `checkAlwaysTrueInstanceof`, `checkAlwaysTrueCheckTypeFunctionCall`, `checkAlwaysTrueStrictComparison` | Always true `instanceof`, type-checking `is_*` functions and strict comparisons `===`/`!==`. These checks can be turned off by setting `checkAlwaysTrueInstanceof`, `checkAlwaysTrueCheckTypeFunctionCall` and `checkAlwaysTrueStrictComparison` to false. |
|                                        | Correct case for referenced and called function names.                                                  |
| `matchingInheritedMethodNames`         | Correct case for inherited and implemented method names.                                                |
|                                        | Contravariance for parameter types and covariance for return types in inherited methods (also known as Liskov substitution principle - LSP).|
|                                        | Check LSP even for static methods.                                                                      |
| `requireParentConstructorCall`         | Require calling parent constructor.                                                                     |
| `disallowedBacktick`                   | Disallow usage of backtick operator (`` $ls = `ls -la` ``).                                             |
| `closureUsesThis`                      | Closure should use `$this` directly instead of using `$this` variable indirectly.                       |

Additional rules are coming in subsequent releases!


## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```
composer require --dev phpstan/phpstan-strict-rules
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include rules.neon in your project's PHPStan config:

```
includes:
    - vendor/phpstan/phpstan-strict-rules/rules.neon
```
</details>

## Disabling rules

You can disable rules using configuration parameters:

```neon
parameters:
	strictRules:
		disallowedLooseComparison: false
		booleansInConditions: false
		booleansInLoopConditions: false
		uselessCast: false
		requireParentConstructorCall: false
		disallowedBacktick: false
		disallowedEmpty: false
		disallowedImplicitArrayCreation: false
		disallowedShortTernary: false
		overwriteVariablesWithLoop: false
		closureUsesThis: false
		matchingInheritedMethodNames: false
		numericOperandsInArithmeticOperators: false
		strictFunctionCalls: false
		dynamicCallOnStaticMethod: false
		switchConditionsMatchingType: false
		noVariableVariables: false
		strictArrayFilter: false
		illegalConstructorMethodCall: false
```

Aside from introducing new custom rules, phpstan-strict-rules also [change the default values of some configuration parameters](./rules.neon#L1) that are present in PHPStan itself. These parameters are [documented on phpstan.org](https://phpstan.org/config-reference#stricter-analysis).

## Enabling rules one-by-one

If you don't want to start using all the available strict rules at once but only one or two, you can!

You can disable all rules from the included `rules.neon` with:

```neon
parameters:
	strictRules:
		allRules: false
```

Then you can re-enable individual rules with configuration parameters:

```neon
parameters:
	strictRules:
		allRules: false
		booleansInConditions: true
```

Even with `strictRules.allRules` set to `false`, part of this package is still in effect. That's because phpstan-strict-rules also [change the default values of some configuration parameters](./rules.neon#L1) that are present in PHPStan itself. These parameters are [documented on phpstan.org](https://phpstan.org/config-reference#stricter-analysis).
