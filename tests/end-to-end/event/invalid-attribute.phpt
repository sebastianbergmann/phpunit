--TEST--
A test method with an attribute that cannot be instantiated is reported as an error without aborting the test run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidAttributeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Event\InvalidAttributeTest::testOne
Invalid attribute PHPUnit\Framework\Attributes\TestWith for method PHPUnit\TestFixture\Event\InvalidAttributeTest::testOne() in %sInvalidAttributeTest.php:18
PHPUnit\Framework\Attributes\TestWith::__construct(): Argument #1 ($data) must be of type array, int given, called in %sInvalidAttributeTest.php on line 17

%sInvalidAttributeTest.php:18

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
