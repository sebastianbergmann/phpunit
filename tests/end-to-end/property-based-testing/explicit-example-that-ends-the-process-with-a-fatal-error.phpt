--TEST--
An explicit example that ends the PHP process with a fatal error is reported with its arguments
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testCompilesCodeThatCannotBeCompiledForExplicitExample';
$_SERVER['argv'][] = __DIR__ . '/_files/ExampleThatEndsTheProcessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234
%A
Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsTheProcessTest::testCompilesCodeThatCannotBeCompiledForExplicitExample.

The PHP process ended while this example of the property was run:

    $value = 500;
