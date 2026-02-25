--TEST--
DataProviderClosure: closure data set has more arguments than test method accepts
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.5.0', PHP_VERSION, '>')) {
    print 'skip: PHP >= 8.5 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/ClosureArgumentCountWarningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\DataProvider\ClosureArgumentCountWarningTest::testOne
Data set #0 provided by callable provided to PHPUnit\TestFixture\DataProvider\ClosureArgumentCountWarningTest::testOne() has more arguments (3) than the test method accepts (2)

%s:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
