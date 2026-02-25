--TEST--
DataProvider: data set value is not an array (integer key)
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/ValueIsNotArrayIntegerKeyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\ValueIsNotArrayIntegerKeyTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\ValueIsNotArrayIntegerKeyTest::testOne is invalid
Data set #0 provided by PHPUnit\TestFixture\DataProvider\ValueIsNotArrayIntegerKeyTest::values is invalid, expected array but got int

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\ValueIsNotArrayIntegerKeyTest".

No tests executed!
