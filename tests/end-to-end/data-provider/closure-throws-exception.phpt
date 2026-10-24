--TEST--
DataProviderClosure: closure throws an exception
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/ClosureThrowsExceptionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\ClosureThrowsExceptionTest::testOne
The data provider callable provided to PHPUnit\TestFixture\DataProvider\ClosureThrowsExceptionTest::testOne() specified for PHPUnit\TestFixture\DataProvider\ClosureThrowsExceptionTest::testOne is invalid
closure failed

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\ClosureThrowsExceptionTest".

No tests executed!
