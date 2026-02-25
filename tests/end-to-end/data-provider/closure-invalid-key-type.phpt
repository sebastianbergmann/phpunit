--TEST--
DataProviderClosure: closure yields a non-int/non-string key
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.5.0', PHP_VERSION, '>')) {
    print 'skip: PHP >= 8.5 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/ClosureInvalidKeyTypeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\ClosureInvalidKeyTypeTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\ClosureInvalidKeyTypeTest::testOne is invalid
The key must be an integer or a string, float given

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\ClosureInvalidKeyTypeTest".

No tests executed!
