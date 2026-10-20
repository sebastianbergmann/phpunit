# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

### [3.5.1] 2026-10-05

  * Fixed `Intervals::isSubsetOf()` returning false for `< 0.0.0`, which now correctly matches nothing and so is a subset of any constraint (#192)

### [3.5.0] 2026-09-24

  * Added `VersionParser::isValid()` to check whether a version string can be normalized (#168)
  * Fixed `Intervals::compactConstraint()` turning constraints that match no dev versions, like `> 1.0.0 != 2.0.0 || < 1.9.0`, into `!= 2.0.0` which matches all `dev-*` versions (#189)
  * Fixed `>=` and `<` constraints on RC versions, like `>=1.0-RC1`, getting an extra `-dev` suffix which `beta` and `alpha` constraints did not get (#187)
  * Improved `CompilingMatcher` performance by avoiding building concatenated cache keys on every call (#186)

### [3.4.4] 2025-08-20

  * Fixed some type annotations to use `list` return types (#171)

### [3.4.3] 2024-09-19

  * Fixed some type annotations

### [3.4.2] 2024-07-12

  * Fixed PHP 5.3 syntax error

### [3.4.1] 2024-07-12

  * Fixed normalizeStability's return type to enforce valid stabilities

### [3.4.0] 2023-08-31

  * Support larger major version numbers (#149)

### [3.3.2] 2022-04-01

  * Fixed handling of non-string values (#134)

### [3.3.1] 2022-03-16

  * Fixed possible cache key clash in the CompilingMatcher memoization (#132)

### [3.3.0] 2022-03-15

  * Improved performance of CompilingMatcher by memoizing more (#131)
  * Added CompilingMatcher::clear to clear all memoization caches

### [3.2.9] 2022-02-04

  * Revert #129 (Fixed MultiConstraint with MatchAllConstraint) which caused regressions

### [3.2.8] 2022-02-04

  * Updates to latest phpstan / CI by @Seldaek in https://github.com/composer/semver/pull/130
  * Fixed MultiConstraint with MatchAllConstraint by @Toflar in https://github.com/composer/semver/pull/129

### [3.2.7] 2022-01-04

  * Fixed: typo in type definition of Intervals class causing issues with Psalm scanning vendors

### [3.2.6] 2021-10-25

  * Fixed: type improvements to parseStability

### [3.2.5] 2021-05-24

  * Fixed: issue comparing disjunctive MultiConstraints to conjunctive ones (#127)
  * Fixed: added complete type information using phpstan annotations

### [3.2.4] 2020-11-13

  * Fixed: code clean-up

### [3.2.3] 2020-11-12

  * Fixed: constraints in the form of `X || Y, >=Y.1` and other such complex constructs were in some cases being optimized into a more restrictive constraint

### [3.2.2] 2020-10-14

  * Fixed: internal code cleanups

### [3.2.1] 2020-09-27

  * Fixed: accidental validation of broken constraints combining ^/~ and wildcards, and -dev suffix allowing weird cases
  * Fixed: normalization of beta0 and such which was dropping the 0

### [3.2.0] 2020-09-09

  * Added: support for `x || @dev`, not very useful but seen in the wild and failed to validate with 1.5.2/1.6.0
  * Added: support for `foobar-dev` being equal to `dev-foobar`, dev-foobar is the official way to write it but we need to support the other for BC and convenience

### [3.1.0] 2020-09-08

  * Added: support for constraints like `^2.x-dev` and `~2.x-dev`, not very useful but seen in the wild and failed to validate with 3.0.1
  * Fixed: invalid aliases will no longer throw, unless explicitly validated by Composer in the root package

### [3.0.1] 2020-09-08

  * Fixed: handling of some invalid -dev versions which were seen as valid

### [3.0.0] 2020-05-26

  * Break: Renamed `EmptyConstraint`, replace it with `MatchAllConstraint`
  * Break: Unlikely to affect anyone but strictly speaking a breaking change, `*.*` and such variants will not match all `dev-*` versions anymore, only `*` does
  * Break: ConstraintInterface is now considered internal/private and not meant to be implemented by third parties anymore
  * Added `Intervals` class to check if a constraint is a subsets of another one, and allow compacting complex MultiConstraints into simpler ones
  * Added `CompilingMatcher` class to speed up constraint matching against simple Constraint instances
  * Added `MatchAllConstraint` and `MatchNoneConstraint` which match everything and nothing
  * Added more advanced optimization of contiguous constraints inside MultiConstraint
  * Added tentative support for PHP 8
  * Fixed ConstraintInterface::matches to be commutative in all cases

### [2.0.0] 2020-04-21

  * Break: `dev-master`, `dev-trunk` and `dev-default` now normalize to `dev-master`, `dev-trunk` and `dev-default` instead of `9999999-dev` in 1.x
  * Break: Removed the deprecated `AbstractConstraint`
  * Added `getUpperBound` and `getLowerBound` to ConstraintInterface. They return `Composer\Semver\Constraint\Bound` instances
  * Added `MultiConstraint::create` to create the most-optimal form of ConstraintInterface from an array of constraint strings

### [1.7.2] 2020-12-03

  * Fixed: Allow installing on php 8

### [1.7.1] 2020-09-27

  * Fixed: accidental validation of broken constraints combining ^/~ and wildcards, and -dev suffix allowing weird cases
  * Fixed: normalization of beta0 and such which was dropping the 0

### [1.7.0] 2020-09-09

  * Added: support for `x || @dev`, not very useful but seen in the wild and failed to validate with 1.5.2/1.6.0
  * Added: support for `foobar-dev` being equal to `dev-foobar`, dev-foobar is the official way to write it but we need to support the other for BC and convenience

### [1.6.0] 2020-09-08

  * Added: support for constraints like `^2.x-dev` and `~2.x-dev`, not very useful but seen in the wild and failed to validate with 1.5.2
  * Fixed: invalid aliases will no longer throw, unless explicitly validated by Composer in the root package

### [1.5.2] 2020-09-08

  * Fixed: handling of some invalid -dev versions which were seen as valid
  * Fixed: some doctypes

### [1.5.1] 2020-01-13

  * Fixed: Parsing of aliased version was not validating the alias to be a valid version

### [1.5.0] 2019-03-19

  * Added: some support for date versions (e.g. 201903) in `~` operator
  * Fixed: support for stabilities in `~` operator was inconsistent

### [1.4.2] 2016-08-30

  * Fixed: collapsing of complex constraints lead to buggy constraints

### [1.4.1] 2016-06-02

  * Changed: branch-like requirements no longer strip build metadata - [composer/semver#38](https://github.com/composer/semver/pull/38).

### [1.4.0] 2016-03-30

  * Added: getters on MultiConstraint - [composer/semver#35](https://github.com/composer/semver/pull/35).

### [1.3.0] 2016-02-25

  * Fixed: stability parsing - [composer/composer#1234](https://github.com/composer/composer/issues/4889).
  * Changed: collapse contiguous constraints when possible.

### [1.2.0] 2015-11-10

  * Changed: allow multiple numerical identifiers in 'pre-release' version part.
  * Changed: add more 'v' prefix support.

### [1.1.0] 2015-11-03

  * Changed: dropped redundant `test` namespace.
  * Changed: minor adjustment in datetime parsing normalization.
  * Changed: `ConstraintInterface` relaxed, setPrettyString is not required anymore.
  * Changed: `AbstractConstraint` marked deprecated, will be removed in 2.0.
  * Changed: `Constraint` is now extensible.

### [1.0.0] 2015-09-21

  * Break: `VersionConstraint` renamed to `Constraint`.
  * Break: `SpecificConstraint` renamed to `AbstractConstraint`.
  * Break: `LinkConstraintInterface` renamed to `ConstraintInterface`.
  * Break: `VersionParser::parseNameVersionPairs` was removed.
  * Changed: `VersionParser::parseConstraints` allows (but ignores) build metadata now.
  * Changed: `VersionParser::parseConstraints` allows (but ignores) prefixing numeric versions with a 'v' now.
  * Changed: Fixed namespace(s) of test files.
  * Changed: `Comparator::compare` no longer throws `InvalidArgumentException`.
  * Changed: `Constraint` now throws `InvalidArgumentException`.

### [0.1.0] 2015-07-23

  * Added: `Composer\Semver\Comparator`, various methods to compare versions.
  * Added: various documents such as README.md, LICENSE, etc.
  * Added: configuration files for Git, Travis, php-cs-fixer, phpunit.
  * Break: the following namespaces were renamed:
    - Namespace: `Composer\Package\Version` -> `Composer\Semver`
    - Namespace: `Composer\Package\LinkConstraint` -> `Composer\Semver\Constraint`
    - Namespace: `Composer\Test\Package\Version` -> `Composer\Test\Semver`
    - Namespace: `Composer\Test\Package\LinkConstraint` -> `Composer\Test\Semver\Constraint`
  * Changed: code style using php-cs-fixer.

[3.5.1]: https://github.com/composer/semver/compare/3.5.0...3.5.1
[3.5.0]: https://github.com/composer/semver/compare/3.4.4...3.5.0
[3.4.4]: https://github.com/composer/semver/compare/3.4.3...3.4.4
[3.4.3]: https://github.com/composer/semver/compare/3.4.2...3.4.3
[3.4.2]: https://github.com/composer/semver/compare/3.4.1...3.4.2
[3.4.1]: https://github.com/composer/semver/compare/3.4.0...3.4.1
[3.4.0]: https://github.com/composer/semver/compare/3.3.2...3.4.0
[3.3.2]: https://github.com/composer/semver/compare/3.3.1...3.3.2
[3.3.1]: https://github.com/composer/semver/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/composer/semver/compare/3.2.9...3.3.0
[3.2.9]: https://github.com/composer/semver/compare/3.2.8...3.2.9
[3.2.8]: https://github.com/composer/semver/compare/3.2.7...3.2.8
[3.2.7]: https://github.com/composer/semver/compare/3.2.6...3.2.7
[3.2.6]: https://github.com/composer/semver/compare/3.2.5...3.2.6
[3.2.5]: https://github.com/composer/semver/compare/3.2.4...3.2.5
[3.2.4]: https://github.com/composer/semver/compare/3.2.3...3.2.4
[3.2.3]: https://github.com/composer/semver/compare/3.2.2...3.2.3
[3.2.2]: https://github.com/composer/semver/compare/3.2.1...3.2.2
[3.2.1]: https://github.com/composer/semver/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/composer/semver/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/composer/semver/compare/3.0.1...3.1.0
[3.0.1]: https://github.com/composer/semver/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/composer/semver/compare/2.0.0...3.0.0
[2.0.0]: https://github.com/composer/semver/compare/1.5.1...2.0.0
[1.7.2]: https://github.com/composer/semver/compare/1.7.1...1.7.2
[1.7.1]: https://github.com/composer/semver/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/composer/semver/compare/1.6.0...1.7.0
[1.6.0]: https://github.com/composer/semver/compare/1.5.2...1.6.0
[1.5.2]: https://github.com/composer/semver/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/composer/semver/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/composer/semver/compare/1.4.2...1.5.0
[1.4.2]: https://github.com/composer/semver/compare/1.4.1...1.4.2
[1.4.1]: https://github.com/composer/semver/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/composer/semver/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/composer/semver/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/composer/semver/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/composer/semver/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/composer/semver/compare/0.1.0...1.0.0
[0.1.0]: https://github.com/composer/semver/compare/5e0b9a4da...0.1.0
