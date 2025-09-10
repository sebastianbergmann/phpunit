# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

For a full diff see [`2.12.0...main`][2.12.0...main].

## [`2.12.0`][2.12.0]

For a full diff see [`2.11.0...2.12.0`][2.11.0...2.12.0].

### Added

- Added support for PHP 8.5 ([#977]), by [@localheinz]

## [`2.11.0`][2.11.0]

For a full diff see [`2.10.5...2.11.0`][2.10.5...2.11.0].

### Changed

- Allowed installation on PHP 8.5 ([#972]), by [@localheinz]

## [`2.10.5`][2.10.5]

For a full diff see [`2.10.4...2.10.5`][2.10.4...2.10.5].

### Fixed

- Adjusted `Methods\NoNamedArgumentRule` to handle calls to constructors of variable class names ([#957]), by [@localheinz]
- Adjusted `Methods\NoNamedArgumentRule` to describe known calls only ([#958]), by [@localheinz]

## [`2.10.4`][2.10.4]

For a full diff see [`2.10.3...2.10.4`][2.10.3...2.10.4].

### Fixed

- Adjusted `Methods\NoNamedArgumentRule` to handle static calls on variable expressions ([#947]), by [@localheinz]
- Adjusted `Methods\NoNamedArgumentRule` to handle calls on invokables ([#948]), by [@localheinz]
- Adjusted `Methods\NoNamedArgumentRule` to handle calls on callables assigned to properties ([#949]), by [@localheinz]
- Adjusted `Methods\NoNamedArgumentRule` to handle all other calls with generic error message ([#951]), by [@localheinz]

## [`2.10.3`][2.10.3]

For a full diff see [`2.10.2...2.10.3`][2.10.2...2.10.3].

### Fixed

- Adjusted `Methods\InvokeParentHookMethodRule` to ignore comments ([#944]), by [@localheinz]

## [`2.10.2`][2.10.2]

For a full diff see [`2.10.1...2.10.2`][2.10.1...2.10.2].

### Fixed

- Renamed error identifier for `Methods\InvokeParentHookMethodRule` ([#943]), by [@localheinz]

## [`2.10.1`][2.10.1]

For a full diff see [`2.10.0...2.10.1`][2.10.0...2.10.1].

### Fixed

- Fixed schema for configuration of `Methods\InvokeParentHookMethodRule` ([#940]), by [@localheinz]

## [`2.10.0`][2.10.0]

For a full diff see [`2.9.0...2.10.0`][2.9.0...2.10.0].

### Added

- Added `Methods\InvokeParentHookMethodRule`, which reports an error when a hook method that overrides a hook method in a parent class does not invoke the overridden hook method in the expected order ([#939]), by [@localheinz]

## [`2.9.0`][2.9.0]

For a full diff see [`2.8.0...2.9.0`][2.8.0...2.9.0].

### Added

- Added `CallLikes\NoNamedArgumentRule`, which reports an error when an anonymous function, a function, or a method is invoked using a named argument ([#914]), by [@localheinz]

### Changed

- Required `phpstan/phpstan:^2.1.8` ([#938]), by [@localheinz]

## [`2.8.0`][2.8.0]

For a full diff see [`2.7.0...2.8.0`][2.7.0...2.8.0].

### Added

- Added `allRules` parameter to allow disabling and enabling all rules ([#913]), by [@localheinz]
- Added `Expressions\NoAssignByReferenceRule`, which reports an error when a variable is assigned by reference ([#914]), by [@localheinz]

## [`2.7.0`][2.7.0]

For a full diff see [`2.6.1...2.7.0`][2.6.1...2.7.0].

### Added

- Added `Closures\NoParameterPassedByReferenceRule`, `Functions\NoParameterPassedByReferenceRule`, `Methods\NoParameterPassedByReferenceRule`, which report an error when a closure, a function, or a method has a parameter that is passed by reference ([#911]), by [@localheinz]
- Added `Functions\NoReturnByReferenceRule` and `Methods\NoReturnByReferenceRule`, which report an error when a function or a method returns by reference ([#912]), by [@localheinz]

## [`2.6.1`][2.6.1]

For a full diff see [`2.6.0...2.6.1`][2.6.0...2.6.1].

### Fixed

- Adjusted `Methods\NoParameterWithNullableTypeDeclarationRule` to use the appropriate error identifier ([#902]), by [@manuelkiessling]

## [`2.6.0`][2.6.0]

For a full diff see [`2.5.2...2.6.0`][2.5.2...2.6.0].

### Added

- Added support for `phpstan/phpstan:^2.0.0`  ([#873]), by [@localheinz]

## [`2.5.2`][2.5.2]

For a full diff see [`2.5.1...2.5.2`][2.5.1...2.5.2].

### Fixed

- Adjusted `Closures\NoNullableReturnTypeDeclarationRule`, `Closures\NoParameterWithNullableTypeDeclarationRule`, `Functions\NoNullableReturnTypeDeclarationRule`, `Functions\NoParameterWithNullableTypeDeclarationRule`, `Methods\NoNullableReturnTypeDeclarationRule`, `Methods\NoParameterWithNullableTypeDeclarationRule` to detect cases where `null` is referenced with incorrect case or relative to the root namespace ([#897]), by [@localheinz]

## [`2.5.1`][2.5.1]

For a full diff see [`2.5.0...2.5.1`][2.5.0...2.5.1].

### Fixed

- Returned rule with error identifier ([#882]), by [@localheinz]
- Adjusted `Methods\FinalInAbstractClassRule` to ignore Doctrine embeddables and entities ([#396]), by [@localheinz]
- Adjusted `Expressions\NoCompactRule` to detect usages of `compact()` with incorrect case ([#889]), by [@localheinz]
- Adjusted `Methods\PrivateInFinalClassRule` to use more appropriate message when detecting a `protected` method in an anonymous class ([#890]), by [@localheinz]
- Adjusted `Methods\PrivateInFinalClassRule` to ignore `protected` methods from traits ([#891]), by [@localheinz]
- Adjusted `Methods\PrivateInFinalClassRule` to ignore `protected` methods with `phpunit/phpunit` attributes requiring methods to be `protected` ([#863]), by [@cosmastech]
- Adjusted `Methods\PrivateInFinalClassRule` to ignore `protected` methods with `phpunit/phpunit` annotations requiring methods to be `protected` ([#895]), by [@cosmastech]

## [`2.5.0`][2.5.0]

For a full diff see [`2.4.0...2.5.0`][2.4.0...2.5.0].

### Added

- Added rule error identifiers ([#875]), by [@localheinz]
- Added support for PHP 8.0 ([#877]), by [@localheinz]
- Added support for PHP 7.4 ([#880]), by [@localheinz]

### Changed

- Removed dependency on `nikic/php-parser` ([#878]), by [@localheinz]

## [`2.4.0`][2.4.0]

For a full diff see [`2.3.0...2.4.0`][2.3.0...2.4.0].

### Added

- Added support for PHP 8.4 ([#872]), by [@localheinz]

## [`2.3.0`][2.3.0]

For a full diff see [`2.2.0...2.3.0`][2.2.0...2.3.0].

### Changed

- Allowed installation on PHP 8.4 ([#862]), by [@localheinz]

## [`2.2.0`][2.2.0]

For a full diff see [`2.1.0...2.2.0`][2.1.0...2.2.0].

### Changed

- Allowed installation of `nikic/php-parser:^5.0.0` ([#735]), by [@localheinz]

## [`2.1.0`][2.1.0]

For a full diff see [`2.0.0...2.1.0`][2.0.0...2.1.0].

### Changed

- Dropped support for PHP 8.0 ([#567]), by [@localheinz]
- Added support for PHP 8.3 ([#604]), by [@nunomaduro]

## [`2.0.0`][2.0.0]

For a full diff see [`1.0.0...2.0.0`][1.0.0...2.0.0].

### Added

- Added `methodsAllowedToUseContainerTypeDeclarations` parameter to allow configuring a list of method names that are allowed to have container parameter type declarations ([#541), by [@localheinz]
- Allowed disabling rules ([#542), by [@localheinz]
- Added support for nullable union types ([#543), by [@localheinz]

### Changed

- Dropped support for PHP 7.2 ([#496]), by [@localheinz]
- Dropped support for PHP 7.3 ([#498]), by [@localheinz]
- Dropped support for PHP 7.4 ([#499]), by [@localheinz]
- Added support for PHP 8.2 ([#540]), by [@localheinz]

### Removed

- Removed `Expressions\NoEmptyRule` ([#525]), by [@enumag]

## [`1.0.0`][1.0.0]

For a full diff see [`0.15.3...1.0.0`][0.15.3...1.0.0].

### Changed

- Added support for `phpstan/phpstan:^1.0.0` and dropped support for non-stable versions of `phpstan/phpstan` ([#381]), by [@rpkamp]

### Fixed

- Adjusted `Classes\FinalRule` to not report an error when a non-final class has a `Doctrinbe\ORM\Mapping\Entity` attribute ([#395]), by [@localheinz]

## [`0.15.3`][0.15.3]

For a full diff see [`0.15.2...0.15.3`][0.15.2...0.15.3].

### Changed

- Allow installation with PHP 8.0 ([#294]), by [@localheinz]

## [`0.15.2`][0.15.2]

For a full diff see [`0.15.1...0.15.2`][0.15.1...0.15.2].

### Changed

- Dropped support for PHP 7.1 ([#259]), by [@localheinz]

## [`0.15.1`][0.15.1]

For a full diff see [`0.15.0...0.15.1`][0.15.0...0.15.1].

### Changed

- Adjusted `Methods\FinalInAbstractClass` rule to allow non-`final` `public` constructors in abstract classes ([#248]), by [@Slamdunk]

## [`0.15.0`][0.15.0]

For a full diff see [`0.14.4...0.15.0`][0.14.4...0.15.0].

### Added

- Added `Classes\PHPUnit\Framework\TestCaseWithSuffixRule`, which reports an error when a concrete class extending `PHPUnit\Framework\TestCase` does not have a `Test` suffix ([#225]), by [@localheinz]

## [`0.14.4`][0.14.4]

For a full diff see [`0.14.3...0.14.4`][0.14.3...0.14.4].

### Fixed

- Ignored classes with `@ORM\Mapping\Entity` annotations in `FinalRule` ([#202]), by [@localheinz]

## [`0.14.3`][0.14.3]

For a full diff see [`0.14.2...0.14.3`][0.14.2...0.14.3].

### Fixed

- Ignored first line in `DeclareStrictTypesRule` when it is a shebang ([#186]), by [@Great-Antique]

## [`0.14.2`][0.14.2]

For a full diff see [`0.14.1...0.14.2`][0.14.1...0.14.2].

### Fixed

- Brought back support for PHP 7.1 ([#166]), by [@localheinz]

## [`0.14.1`][0.14.1]

For a full diff see [`0.14.0...0.14.1`][0.14.0...0.14.1].

### Fixed

- Removed an inappropriate `replace` configuration from `composer.json` ([#161]), by [@localheinz]

## [`0.14.0`][0.14.0]

For a full diff see [`0.13.0...0.14.0`][0.13.0...0.14.0].

### Changed

- Allowed installation of `phpstan/phpstan:~0.12.0`  ([#147]), by [@localheinz]
- Renamed vendor namespace `Localheinz` to `Ergebnis` after move to [@ergebnis] ([#157]), by [@localheinz]

  Run

  ```sh
  composer remove localheinz/phpstan-rules
  ```

  and

  ```sh
  composer require ergebnis/phpstan-rules
  ```

  to update.

  Run

  ```sh
  find . -type f -exec sed -i '.bak' 's/Localheinz\\PHPStan/Ergebnis\\PHPStan/g' {} \;
  ```

  to replace occurrences of `Localheinz\PHPStan` with `Ergebnis\PHPStan`.

  Run

  ```sh
  find -type f -name '*.bak' -delete
  ```

  to delete backup files created in the previous step.

- Moved parameters into `ergebnis` section to prevent conflicts with global parameters ([#158]), by [@localheinz]

  Where previously `phpstan.neon` looked like the following

  ```neon
  parameters:
      allowAbstractClasses: true
      classesAllowedToBeExtended: []
      classesNotRequiredToBeAbstractOrFinal: []
      interfacesImplementedByContainers:
          - Psr\Container\ContainerInterface
  ```

  these parameters now need to be moved into an `ergebnis` section:

  ```diff
   parameters:
  -    allowAbstractClasses: true
  -    classesAllowedToBeExtended: []
  -    classesNotRequiredToBeAbstractOrFinal: []
  -    interfacesImplementedByContainers:
  -        - Psr\Container\ContainerInterface
  +    ergebnis:
  +        allowAbstractClasses: true
  +        classesAllowedToBeExtended: []
  +        classesNotRequiredToBeAbstractOrFinal: []
  +        interfacesImplementedByContainers:
  +            - Psr\Container\ContainerInterface
  ```

### Fixed

- Dropped support for PHP 7.1 ([#141]), by [@localheinz]

## [`0.13.0`][0.13.0]

For a full diff see [`0.12.2...0.13.0`][0.12.2...0.13.0].

### Added

- Added `Methods\PrivateInFinalClassRule` which reports an error when a method in a `final` class is `protected` when it could be `private` ([#126]), by [@localheinz]

## [`0.12.2`][0.12.2]

For a full diff see [`0.12.1...0.12.2`][0.12.1...0.12.2].

### Fixed

- Started ignoring interfaces from analysis by `Methods\FinalInAbstractClassRule` to avoid inappropriate errors ([#132]), by [@localheinz]

## [`0.12.1`][0.12.1]

For a full diff see [`0.12.0...0.12.1`][0.12.0...0.12.1].

### Fixed

- Started resolving class name in type declaration before attempting to analyze it in the `Methods\NoParameterWithContainerTypeDeclarationRule` to avoid errors where class `self` is not found ([#128]), by [@localheinz]

## [`0.12.0`][0.12.0]

For a full diff see [`0.11.0...0.12.0`][0.11.0...0.12.0].

### Added

- Added `Methods\NoParameterWithContainerTypeDeclarationRule`, which reports an error when a method has a type declaration that corresponds to a known dependency injection container or service locator ([#122]), by [@localheinz]
- Added `Methods\FinalInAbstractClassRule`, which reports an error when a concrete `public` or `protected` method in an `abstract` class is not `final` ([#123]), by [@localheinz]

## [`0.11.0`][0.11.0]

For a full diff see [`0.10.0...0.11.0`][0.10.0...0.11.0].

### Added

- Added `Files\DeclareStrictTypesRule`, which reports an error when a PHP file does not have a `declare(strict_types=1)` declaration ([#79]
- Added `Expressions\NoEmptyRule`, which reports an error when the language construct `empty()` is used ([#110]), by [@localheinz]
- Added `Expressions\NoEvalRule`, which reports an error when the language construct `eval()` is used ([#112]), by [@localheinz]
- Added `Expressions\NoErrorSuppressionRule`, which reports an error when `@` is used to suppress errors ([#113]), by [@localheinz]
- Added `Expressions\NoCompactRule`, which reports an error when the function `compact()` is used ([#116]), by [@localheinz]
- Added `Statements\NoSwitchRule`, which reports an error when the statement `switch()` is used ([#117]), by [@localheinz]

### Changed

- Require at least `nikic/php-parser:^4.2.3` ([#102]), by [@localheinz]
- Require at least `phpstan/phpstan:~0.11.15` ([#103]), by [@localheinz]

## [`0.10.0`][0.10.0]

For a full diff see [`0.9.1...0.10.0`][0.9.1...0.10.0].

### Changed

- Require at least `phpstan/phpstan:~0.11.7` ([#91]), by [@localheinz]

### Fixed

- Added missing `parametersSchema` configuration to `rules.neon`, which is required for use with `bleedingEdge.neon`, see [`phpstan/phpstan@54a125d`](https://github.com/phpstan/phpstan/commit/54a125df47fa097b792cb9a3e70c49f753f66b85) ([#93]), by [@localheinz]
*
## [`0.9.1`][0.9.1]

For a full diff see [`0.9.0...0.9.1`][0.9.0...0.9.1].

### Changed

- Allow usage with [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer) ([#89]), by [@localheinz]

## [`0.9.0`][0.9.0]

For a full diff see [`0.8.1...0.9.0`][0.8.1...0.9.0].

### Changed

- Adjusted `Classes\FinalRule` to ignore Doctrine entities when they are annotated ([#84]), by [@localheinz]

## [`0.8.1`][0.8.1]

For a full diff see [`0.8.0...0.8.1`][0.8.0...0.8.1].

### Fixed

- Actually enable `Expressions\NoIssetRule` ([#83]), by [@localheinz]

## [`0.8.0`][0.8.0]

For a full diff see [`0.7.1...0.8.0`][0.7.1...0.8.0].

### Added

- Added `Expressions\NoIssetRule`, which reports an error when the language construct `isset()` is used ([#81]), by [@localheinz]

## [`0.7.1`][0.7.1]

For a full diff see [`0.7.0...0.7.1`][0.7.0...0.7.1].

### Changed

- Configured `Classes\NoExtendsRule` to allow a set of default classes to be extended ([#73]), by [@localheinz]

## [`0.7.0`][0.7.0]

For a full diff see [`0.6.0...0.7.0`][0.6.0...0.7.0].

### Added

- Added `Classes\NoExtendsRule`, which reports an error when a class extends a class that is not allowed to be extended ([#68]), by [@localheinz]

## [`0.6.0`][0.6.0]

For a full diff see [`0.5.0...0.6.0`][0.5.0...0.6.0].

### Added

- Allow installation with `phpstan/phpstan:~0.11.0` ([#65]), by [@localheinz]

## [`0.5.0`][0.5.0]

For a full diff see [`0.4.0...0.5.0`][0.4.0...0.5.0].

### Added

- Added `Methods\NoConstructorParameterWithDefaultValueRule`, which reports an error when a constructor of an anonymous class or a class has a parameter with a default value ([#45]), by [@localheinz]
- Added parameters `$allowAbstractClasses` and `$classesNotRequiredToBeAbstractOrFinal` to allow configuration of `Classes`FinalRule` ([#51]), by [@localheinz]

### Removed

- Removed `Classes\AbstractOrFinalRule` after merging behaviour into `Classes\FinalRule` ([#51]), by [@localheinz]
- Removed default values from constructor of `Classes\FinalRule` ([#53]), by [@localheinz]

## [`0.4.0`][0.4.0]

For a full diff see [`0.3.0...0.4.0`][0.3.0...0.4.0]

### Changed

- Removed double-quotes from error messages to be more consistent with error messages generated by `phpstan/phstan` ([#39]), by [@localheinz]
- Modified wording and placement of method, function, and parameter names in error messages to be more consistent with error messages generated by `phpstan/phstan` ([#42]), by [@localheinz]

## [`0.3.0`][0.3.0]

For a full diff see [`0.2.0...0.3.0`][0.2.0...0.3.0]

### Added

- Added `Functions\NoNullableReturnTypeDeclarationRule`, which reports an error when a function has a nullable return type declaration, and `Methods\NoNullableReturnTypeDeclarationRule`, which reports an error when a method declared in an anonymous class, a class, or an interface has a nullable return type declaration ([#16]), by [@localheinz]
- Added `Closures\NoParameterWithNullDefaultValueRule`, which reports an error when a closure has a parameter with `null` as default value ([#26]), by [@localheinz]
- Added `Closures\NoNullableReturnTypeDeclarationRule`, which reports an error when a closure has a nullable return type declaration ([#29]), by [@localheinz]
- Added `Functions\NoParameterWithNullDefaultValueRule`, which reports an error when a function has a parameter with `null` as default value ([#31]), by [@localheinz]
- Added `Methods\NoParameterWithNullDefaultValueRule`, which reports an error when a method declared in an anonymous class, a class, or an interface has a parameter with `null` as default value ([#32]), by [@localheinz]
- Added `Closures\NoParameterWithNullableTypeDeclarationRule`, which reports an error when a closure has a parameter with a nullable type declaration ([#33]), by [@localheinz]
- Added `Functions\NoParameterWithNullableTypeDeclarationRule`, which reports an error when a function has a parameter with a nullable type declaration ([#34]), by [@localheinz]
- Added `Methods\NoParameterWithNullableTypeDeclarationRule`, which reports an error when a method declared in an anonymous class, a class, or an interface has a parameter with a nullable type declaration ([#35]), by [@localheinz]
- Extracted `rules.neon`, so you can easily enable all rules by including it in your `phpstan.neon` ([#37]), by [@localheinz]

## [`0.2.0`][0.2.0]

For a full diff see [`0.1.0...0.2.0`][0.1.0...0.2.0]

### Added

- Added `Classes\FinalRule`, which reports an error when a non-anonymous class is not `final`, ([#4]), by [@localheinz]

### Changed

- Added an `$excludeClassNames` argument to the constructors of `Classes\AbstractOrFinalRule` and `Classes\FinalRule` to allow whitelisting of classes, ([#11]), by [@localheinz]

## [`0.1.0`][0.1.0]

For a full diff see [`362c7ea...0.1.0`][362c7ea...0.1.0].

### Added

- Added `Classes\AbstractOrFinalRule`, which reports an error when a non-anonymous class is neither `abstract` nor `final`, ([#1]), by [@localheinz]

[0.1.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.1.0
[0.2.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.2.0
[0.3.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.3.0
[0.4.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.4.0
[0.5.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.5.0
[0.6.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.6.0
[0.7.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.7.0
[0.7.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.7.1
[0.8.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.8.0
[0.8.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.8.1
[0.9.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.9.0
[0.9.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.9.1
[0.10.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.10.0
[0.11.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.11.0
[0.12.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.12.0
[0.12.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.12.1
[0.12.2]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.12.2
[0.13.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.13.0
[0.14.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.14.0
[0.14.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.14.1
[0.14.2]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.14.2
[0.14.3]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.14.3
[0.14.4]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.14.4
[0.15.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.15.0
[0.15.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.15.1
[0.15.2]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.15.2
[0.15.3]: https://github.com/ergebnis/phpstan-rules/releases/tag/0.15.3
[1.0.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/1.0.0
[2.0.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.0.0
[2.1.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.1.0
[2.2.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.2.0
[2.3.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.3.0
[2.4.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.4.0
[2.5.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.5.0
[2.5.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.5.1
[2.5.2]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.5.2
[2.6.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.6.0
[2.6.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.6.1
[2.7.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.7.0
[2.8.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.8.0
[2.9.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.9.0
[2.10.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.10.0
[2.10.1]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.10.1
[2.10.2]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.10.2
[2.10.3]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.10.3
[2.10.4]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.10.4
[2.10.5]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.10.5
[2.11.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.11.0
[2.12.0]: https://github.com/ergebnis/phpstan-rules/releases/tag/2.12.0

[362c7ea...0.1.0]: https://github.com/ergebnis/phpstan-rules/compare/362c7ea...0.1.0
[0.1.0...0.2.0]: https://github.com/ergebnis/phpstan-rules/compare/0.1.0...0.2.0
[0.2.0...0.3.0]: https://github.com/ergebnis/phpstan-rules/compare/0.2.0...0.3.0
[0.3.0...0.4.0]: https://github.com/ergebnis/phpstan-rules/compare/0.3.0...0.4.0
[0.4.0...0.5.0]: https://github.com/ergebnis/phpstan-rules/compare/0.4.0...0.5.0
[0.5.0...0.6.0]: https://github.com/ergebnis/phpstan-rules/compare/0.5.0...0.6.0
[0.6.0...0.7.0]: https://github.com/ergebnis/phpstan-rules/compare/0.6.0...0.7.0
[0.7.0...0.7.1]: https://github.com/ergebnis/phpstan-rules/compare/0.7.0...0.7.1
[0.7.1...0.8.0]: https://github.com/ergebnis/phpstan-rules/compare/0.7.1...0.8.0
[0.8.0...0.8.1]: https://github.com/ergebnis/phpstan-rules/compare/0.8.0...0.8.1
[0.8.1...0.9.0]: https://github.com/ergebnis/phpstan-rules/compare/0.8.1...0.9.0
[0.9.0...0.9.1]: https://github.com/ergebnis/phpstan-rules/compare/0.9.0...0.9.1
[0.9.1...0.10.0]: https://github.com/ergebnis/phpstan-rules/compare/0.9.1...0.10.0
[0.10.0...0.11.0]: https://github.com/ergebnis/phpstan-rules/compare/0.10.0...0.11.0
[0.11.0...0.12.0]: https://github.com/ergebnis/phpstan-rules/compare/0.11.0...0.12.0
[0.12.0...0.12.1]: https://github.com/ergebnis/phpstan-rules/compare/0.12.0...0.12.1
[0.12.1...0.12.2]: https://github.com/ergebnis/phpstan-rules/compare/0.12.1...0.12.2
[0.12.2...0.13.0]: https://github.com/ergebnis/phpstan-rules/compare/0.12.2...0.13.0
[0.13.0...0.14.0]: https://github.com/ergebnis/phpstan-rules/compare/0.13.0...0.14.0
[0.14.0...0.14.1]: https://github.com/ergebnis/phpstan-rules/compare/0.14.0...0.14.1
[0.14.1...0.14.2]: https://github.com/ergebnis/phpstan-rules/compare/0.14.1...0.14.2
[0.14.2...0.14.3]: https://github.com/ergebnis/phpstan-rules/compare/0.14.2...0.14.3
[0.14.3...0.14.4]: https://github.com/ergebnis/phpstan-rules/compare/0.14.3...0.14.4
[0.14.4...0.15.0]: https://github.com/ergebnis/phpstan-rules/compare/0.14.4...0.15.0
[0.15.0...0.15.1]: https://github.com/ergebnis/phpstan-rules/compare/0.15.0...0.15.1
[0.15.1...0.15.2]: https://github.com/ergebnis/phpstan-rules/compare/0.15.1...0.15.2
[0.15.2...0.15.3]: https://github.com/ergebnis/phpstan-rules/compare/0.15.2...0.15.3
[0.15.3...1.0.0]: https://github.com/ergebnis/phpstan-rules/compare/0.15.3...1.0.0
[1.0.0...2.0.0]: https://github.com/ergebnis/phpstan-rules/compare/1.0.0...2.0.0
[2.0.0...2.1.0]: https://github.com/ergebnis/phpstan-rules/compare/2.0.0...2.1.0
[2.1.0...2.2.0]: https://github.com/ergebnis/phpstan-rules/compare/2.1.0...2.2.0
[2.2.0...2.3.0]: https://github.com/ergebnis/phpstan-rules/compare/2.2.0...2.3.0
[2.3.0...2.4.0]: https://github.com/ergebnis/phpstan-rules/compare/2.3.0...2.4.0
[2.4.0...2.5.0]: https://github.com/ergebnis/phpstan-rules/compare/2.4.0...2.5.0
[2.5.0...2.5.1]: https://github.com/ergebnis/phpstan-rules/compare/2.5.0...2.5.1
[2.5.1...2.5.2]: https://github.com/ergebnis/phpstan-rules/compare/2.5.1...2.5.2
[2.5.2...2.6.0]: https://github.com/ergebnis/phpstan-rules/compare/2.5.2...2.6.0
[2.6.0...2.6.1]: https://github.com/ergebnis/phpstan-rules/compare/2.6.0...2.6.1
[2.6.1...2.7.0]: https://github.com/ergebnis/phpstan-rules/compare/2.6.1...2.7.0
[2.7.0...2.8.0]: https://github.com/ergebnis/phpstan-rules/compare/2.7.0...2.8.0
[2.8.0...2.9.0]: https://github.com/ergebnis/phpstan-rules/compare/2.8.0...2.9.0
[2.9.0...2.10.0]: https://github.com/ergebnis/phpstan-rules/compare/2.9.0...2.10.0
[2.10.0...2.10.1]: https://github.com/ergebnis/phpstan-rules/compare/2.10.0...2.10.1
[2.10.1...2.10.2]: https://github.com/ergebnis/phpstan-rules/compare/2.10.1...2.10.2
[2.10.2...2.10.3]: https://github.com/ergebnis/phpstan-rules/compare/2.10.2...2.10.3
[2.10.3...2.10.4]: https://github.com/ergebnis/phpstan-rules/compare/2.10.3...2.10.4
[2.10.4...2.10.5]: https://github.com/ergebnis/phpstan-rules/compare/2.10.4...2.10.5
[2.10.5...2.11.0]: https://github.com/ergebnis/phpstan-rules/compare/2.10.5...2.11.0
[2.11.0...2.12.0]: https://github.com/ergebnis/phpstan-rules/compare/2.11.0...2.12.0
[2.12.0...main]: https://github.com/ergebnis/phpstan-rules/compare/2.12.0...main

[#1]: https://github.com/ergebnis/phpstan-rules/pull/1
[#4]: https://github.com/ergebnis/phpstan-rules/pull/4
[#11]: https://github.com/ergebnis/phpstan-rules/pull/11
[#16]: https://github.com/ergebnis/phpstan-rules/pull/16
[#26]: https://github.com/ergebnis/phpstan-rules/pull/26
[#29]: https://github.com/ergebnis/phpstan-rules/pull/29
[#31]: https://github.com/ergebnis/phpstan-rules/pull/31
[#32]: https://github.com/ergebnis/phpstan-rules/pull/32
[#33]: https://github.com/ergebnis/phpstan-rules/pull/33
[#34]: https://github.com/ergebnis/phpstan-rules/pull/34
[#35]: https://github.com/ergebnis/phpstan-rules/pull/35
[#37]: https://github.com/ergebnis/phpstan-rules/pull/37
[#39]: https://github.com/ergebnis/phpstan-rules/pull/39
[#42]: https://github.com/ergebnis/phpstan-rules/pull/42
[#45]: https://github.com/ergebnis/phpstan-rules/pull/45
[#51]: https://github.com/ergebnis/phpstan-rules/pull/51
[#53]: https://github.com/ergebnis/phpstan-rules/pull/53
[#65]: https://github.com/ergebnis/phpstan-rules/pull/65
[#68]: https://github.com/ergebnis/phpstan-rules/pull/68
[#73]: https://github.com/ergebnis/phpstan-rules/pull/73
[#79]: https://github.com/ergebnis/phpstan-rules/pull/79
[#81]: https://github.com/ergebnis/phpstan-rules/pull/81
[#83]: https://github.com/ergebnis/phpstan-rules/pull/83
[#84]: https://github.com/ergebnis/phpstan-rules/pull/84
[#89]: https://github.com/ergebnis/phpstan-rules/pull/89
[#91]: https://github.com/ergebnis/phpstan-rules/pull/91
[#93]: https://github.com/ergebnis/phpstan-rules/pull/93
[#102]: https://github.com/ergebnis/phpstan-rules/pull/102
[#103]: https://github.com/ergebnis/phpstan-rules/pull/103
[#110]: https://github.com/ergebnis/phpstan-rules/pull/110
[#112]: https://github.com/ergebnis/phpstan-rules/pull/112
[#113]: https://github.com/ergebnis/phpstan-rules/pull/113
[#116]: https://github.com/ergebnis/phpstan-rules/pull/116
[#117]: https://github.com/ergebnis/phpstan-rules/pull/117
[#122]: https://github.com/ergebnis/phpstan-rules/pull/122
[#123]: https://github.com/ergebnis/phpstan-rules/pull/123
[#126]: https://github.com/ergebnis/phpstan-rules/pull/126
[#128]: https://github.com/ergebnis/phpstan-rules/pull/128
[#132]: https://github.com/ergebnis/phpstan-rules/pull/132
[#141]: https://github.com/ergebnis/phpstan-rules/pull/141
[#147]: https://github.com/ergebnis/phpstan-rules/pull/147
[#157]: https://github.com/ergebnis/phpstan-rules/pull/157
[#158]: https://github.com/ergebnis/phpstan-rules/pull/158
[#161]: https://github.com/ergebnis/phpstan-rules/pull/161
[#166]: https://github.com/ergebnis/phpstan-rules/pull/166
[#186]: https://github.com/ergebnis/phpstan-rules/pull/186
[#202]: https://github.com/ergebnis/phpstan-rules/pull/202
[#225]: https://github.com/ergebnis/phpstan-rules/pull/225
[#248]: https://github.com/ergebnis/phpstan-rules/pull/248
[#259]: https://github.com/ergebnis/phpstan-rules/pull/259
[#294]: https://github.com/ergebnis/phpstan-rules/pull/294
[#381]: https://github.com/ergebnis/phpstan-rules/pull/381
[#395]: https://github.com/ergebnis/phpstan-rules/pull/395
[#396]: https://github.com/ergebnis/phpstan-rules/pull/396
[#496]: https://github.com/ergebnis/phpstan-rules/pull/496
[#498]: https://github.com/ergebnis/phpstan-rules/pull/498
[#499]: https://github.com/ergebnis/phpstan-rules/pull/498
[#525]: https://github.com/ergebnis/phpstan-rules/pull/525
[#540]: https://github.com/ergebnis/phpstan-rules/pull/540
[#541]: https://github.com/ergebnis/phpstan-rules/pull/541
[#542]: https://github.com/ergebnis/phpstan-rules/pull/542
[#543]: https://github.com/ergebnis/phpstan-rules/pull/543
[#567]: https://github.com/ergebnis/phpstan-rules/pull/567
[#735]: https://github.com/ergebnis/phpstan-rules/pull/735
[#862]: https://github.com/ergebnis/phpstan-rules/pull/862
[#863]: https://github.com/ergebnis/phpstan-rules/pull/863
[#872]: https://github.com/ergebnis/phpstan-rules/pull/872
[#873]: https://github.com/ergebnis/phpstan-rules/pull/873
[#875]: https://github.com/ergebnis/phpstan-rules/pull/875
[#877]: https://github.com/ergebnis/phpstan-rules/pull/877
[#878]: https://github.com/ergebnis/phpstan-rules/pull/878
[#880]: https://github.com/ergebnis/phpstan-rules/pull/880
[#882]: https://github.com/ergebnis/phpstan-rules/pull/882
[#889]: https://github.com/ergebnis/phpstan-rules/pull/889
[#890]: https://github.com/ergebnis/phpstan-rules/pull/890
[#891]: https://github.com/ergebnis/phpstan-rules/pull/891
[#895]: https://github.com/ergebnis/phpstan-rules/pull/895
[#897]: https://github.com/ergebnis/phpstan-rules/pull/897
[#902]: https://github.com/ergebnis/phpstan-rules/pull/902
[#911]: https://github.com/ergebnis/phpstan-rules/pull/911
[#912]: https://github.com/ergebnis/phpstan-rules/pull/912
[#913]: https://github.com/ergebnis/phpstan-rules/pull/913
[#914]: https://github.com/ergebnis/phpstan-rules/pull/914
[#938]: https://github.com/ergebnis/phpstan-rules/pull/938
[#939]: https://github.com/ergebnis/phpstan-rules/pull/939
[#940]: https://github.com/ergebnis/phpstan-rules/pull/940
[#943]: https://github.com/ergebnis/phpstan-rules/pull/943
[#944]: https://github.com/ergebnis/phpstan-rules/pull/944
[#947]: https://github.com/ergebnis/phpstan-rules/pull/947
[#948]: https://github.com/ergebnis/phpstan-rules/pull/948
[#949]: https://github.com/ergebnis/phpstan-rules/pull/949
[#951]: https://github.com/ergebnis/phpstan-rules/pull/951
[#957]: https://github.com/ergebnis/phpstan-rules/pull/957
[#958]: https://github.com/ergebnis/phpstan-rules/pull/958
[#972]: https://github.com/ergebnis/phpstan-rules/pull/972
[#977]: https://github.com/ergebnis/phpstan-rules/pull/977

[@cosmastech]: https://github.com/cosmastech
[@enumag]: https://github.com/enumag
[@ergebnis]: https://github.com/ergebnis
[@Great-Antique]: https://github.com/Great-Antique
[@localheinz]: https://github.com/localheinz
[@manuelkiessling]: https://github.com/manuelkiessling
[@nunomaduro]: https://github.com/nunomaduro
[@rpkamp]: https://github.com/rpkamp
[@Slamdunk]: https://github.com/Slamdunk
