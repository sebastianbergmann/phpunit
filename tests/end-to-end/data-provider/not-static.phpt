--TEST--
DataProvider: provider method is not static
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/NotStaticTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\NotStaticTest::testOne
The data provider PHPUnit\TestFixture\DataProvider\NotStaticTest::values specified for PHPUnit\TestFixture\DataProvider\NotStaticTest::testOne is invalid
Data Provider method PHPUnit\TestFixture\DataProvider\NotStaticTest::values() is not static

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\NotStaticTest".

No tests executed!
