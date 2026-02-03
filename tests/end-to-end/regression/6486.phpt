--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6486
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/6486/Issue6486Trait.php';
$_SERVER['argv'][] = __DIR__ . '/6486/Issue6486Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue6486\Issue6486Test::testWithDataProviderThatDoesNotExist
The data provider specified for PHPUnit\TestFixture\Issue6486\Issue6486Test::testWithDataProviderThatDoesNotExist is invalid
Method PHPUnit\TestFixture\Issue6486\Issue6486Test::Abracadabra() does not exist

%sIssue6486Trait.php:22

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
