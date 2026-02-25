--TEST--
DataProvider: provider method is not public
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/NotPublicTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\NotPublicTest::testOne
The data provider PHPUnit\TestFixture\DataProvider\NotPublicTest::values specified for PHPUnit\TestFixture\DataProvider\NotPublicTest::testOne is invalid
Data Provider method PHPUnit\TestFixture\DataProvider\NotPublicTest::values() is not public

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\NotPublicTest".

No tests executed!
