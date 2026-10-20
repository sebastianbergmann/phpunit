# Changelog

## 3.2.0 (2024-05-24)

*   Feature: Improve PHP 8.4+ support by avoiding implicitly nullable type declarations.
    (#260 by @Ayesh)

*   Feature: Include previous exceptions when reporting unhandled promise rejections.
    (#262 by @clue)

*   Update test suite to improve PHP 8.4+ support.
    (#261 by @SimonFrings)

## 3.1.0 (2023-11-16)

*   Feature: Full PHP 8.3 compatibility.
    (#255 by @clue)

*   Feature: Describe all callable arguments with types for `Promise` and `Deferred`.
    (#253 by @clue)

*   Update test suite and minor documentation improvements.
    (#251 by @ondrejmirtes and #250 by @SQKo)

## 3.0.0 (2023-07-11)

A major new feature release, see [**release announcement**](https://clue.engineering/2023/announcing-reactphp-promise-v3).

*   We'd like to emphasize that this component is production ready and battle-tested.
    We plan to support all long-term support (LTS) releases for at least 24 months,
    so you have a rock-solid foundation to build on top of.

*   The v3 release will be the way forward for this package. However, we will still
    actively support v2 and v1 to provide a smooth upgrade path for those not yet
    on the latest versions.

This update involves some major new features and a minor BC break over the
`v2.0.0` release. We've tried hard to avoid BC breaks where possible and
minimize impact otherwise. We expect that most consumers of this package will be
affected by BC breaks, but updating should take no longer than a few minutes.
See below for more details:

*   BC break: PHP 8.1+ recommended, PHP 7.1+ required.
    (#138 and #149 by @WyriHaximus)

*   Feature / BC break: The `PromiseInterface` now includes the functionality of the old ~~`ExtendedPromiseInterface`~~ and ~~`CancellablePromiseInterface`~~.
    Each promise now always includes the `then()`, `catch()`, `finally()` and `cancel()` methods.
    The new `catch()` and `finally()` methods replace the deprecated ~~`otherwise()`~~ and ~~`always()`~~ methods which continue to exist for BC reasons.
    The old ~~`ExtendedPromiseInterface`~~ and ~~`CancellablePromiseInterface`~~ are no longer needed and have been removed as a consequence.
    (#75 by @jsor and #208 by @clue and @WyriHaximus)

    ```php
    // old (multiple interfaces may or may not be implemented)
    assert($promise instanceof PromiseInterface);
    assert(method_exists($promise, 'then'));
    if ($promise instanceof ExtendedPromiseInterface) { assert(method_exists($promise, 'otherwise')); }
    if ($promise instanceof ExtendedPromiseInterface) { assert(method_exists($promise, 'always')); }
    if ($promise instanceof CancellablePromiseInterface) { assert(method_exists($promise, 'cancel')); }
    
    // new (single PromiseInterface with all methods)
    assert($promise instanceof PromiseInterface);
    assert(method_exists($promise, 'then'));
    assert(method_exists($promise, 'catch'));
    assert(method_exists($promise, 'finally'));
    assert(method_exists($promise, 'cancel'));
    ```

*   Feature / BC break: Improve type safety of promises. Require `mixed` fulfillment value argument and `Throwable` (or `Exception`) as rejection reason.
    Add PHPStan template types to ensure strict types for `resolve(T $value): PromiseInterface<T>` and `reject(Throwable $reason): PromiseInterface<never>`.
    It is no longer possible to resolve a promise without a value (use `null` instead) or reject a promise without a reason (use `Throwable` instead).
    (#93, #141 and #142 by @jsor, #138, #149 and #247 by @WyriHaximus and #213 and #246 by @clue)

    ```php
    // old (arguments used to be optional)
    $promise = resolve();
    $promise = reject();
    
    // new (already supported before)
    $promise = resolve(null);
    $promise = reject(new RuntimeException());
    ```

*   Feature / BC break: Report all unhandled rejections by default and remove ~~`done()`~~ method.
    Add new `set_rejection_handler()` function to set the global rejection handler for unhandled promise rejections.
    (#248, #249 and #224 by @clue)

    ```php
    // Unhandled promise rejection with RuntimeException: Unhandled in example.php:2
    reject(new RuntimeException('Unhandled'));
    ```

*   BC break: Remove all deprecated APIs and reduce API surface.
    Remove ~~`some()`~~, ~~`map()`~~, ~~`reduce()`~~ functions, use `any()` and `all()` functions instead.
    Remove internal ~~`FulfilledPromise`~~ and ~~`RejectedPromise`~~ classes, use `resolve()` and `reject()` functions instead.
    Remove legacy promise progress API (deprecated third argument to `then()` method) and deprecated ~~`LazyPromise`~~ class. 
    (#32 and #98 by @jsor and #164, #219 and #220 by @clue)

*   BC break: Make all classes final to encourage composition over inheritance.
    (#80 by @jsor)

*   Feature / BC break: Require `array` (or `iterable`) type for `all()` + `race()` + `any()` functions and bring in line with ES6 specification.
    These functions now require a single argument with a variable number of promises or values as input.
    (#225 by @clue and #35 by @jsor)

*   Fix / BC break: Fix `race()` to return a forever pending promise when called with an empty `array` (or `iterable`) and bring in line with ES6 specification.
    (#83 by @jsor and #225 by @clue)

*   Minor performance improvements by initializing `Deferred` in the constructor and avoiding `call_user_func()` calls.
    (#151 by @WyriHaximus and #171 by @Kubo2)

*   Minor documentation improvements.
    (#110 by @seregazhuk, #132 by @CharlotteDunois, #145 by @danielecr, #178 by @WyriHaximus, #189 by @srdante, #212 by @clue, #214, #239 and #243 by @SimonFrings and #231 by @nhedger)

The following changes had to be ported to this release due to our branching
strategy, but also appeared in the [`2.x` branch](https://github.com/reactphp/promise/tree/2.x):

*   Feature: Support union types and address deprecation of `ReflectionType::getClass()` (PHP 8+).
    (#197 by @cdosoftei and @SimonFrings)

*   Feature: Support intersection types (PHP 8.1+).
    (#209 by @bzikarsky)

*   Feature: Support DNS types (PHP 8.2+).
    (#236 by @nhedger)

*   Feature: Port all memory improvements from `2.x` to `3.x`.
    (#150 by @clue and @WyriHaximus)

*   Fix: Fix checking whether cancellable promise is an object and avoid possible warning.
    (#161 by @smscr)

*   Improve performance by prefixing all global functions calls with \ to skip the look up and resolve process and go straight to the global function.
    (#134 by @WyriHaximus)

*   Improve test suite, update PHPUnit and PHP versions and add `.gitattributes` to exclude dev files from exports.
    (#107 by @carusogabriel, #148 and #234 by @WyriHaximus, #153 by @reedy, #162, #230 and #240 by @clue, #173, #177, #185 and #199 by @SimonFrings, #193 by @woodongwong and #210 by @bzikarsky)

The following changes were originally planned for this release but later reverted
and are not part of the final release:

*   Add iterative callback queue handler to avoid recursion (later removed to improve Fiber support). 
    (#28, #82 and #86 by @jsor, #158 by @WyriHaximus and #229 and #238 by @clue)

*   Trigger an `E_USER_ERROR` instead of throwing an exception from `done()` (later removed entire `done()` method to globally report unhandled rejections).
    (#97 by @jsor and #224 and #248 by @clue)

*   Add type declarations for `some()` (later removed entire `some()` function).
    (#172 by @WyriHaximus and #219 by @clue)

## 2.0.0 (2013-12-10)

See [`2.x` CHANGELOG](https://github.com/reactphp/promise/blob/2.x/CHANGELOG.md) for more details.

## 1.0.0 (2012-11-07)

See [`1.x` CHANGELOG](https://github.com/reactphp/promise/blob/1.x/CHANGELOG.md) for more details.
