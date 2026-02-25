--TEST--
TestWithJson: JSON value that decodes to a non-array triggers an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/TestWithValueIsNotArrayTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\TestWithValueIsNotArrayTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\TestWithValueIsNotArrayTest::testOne is invalid
Data set #0 provided by TestWith#0 attribute is invalid, expected array but got bool

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\TestWithValueIsNotArrayTest".

No tests executed!
