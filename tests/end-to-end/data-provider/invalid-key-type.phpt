--TEST--
DataProvider: provider yields a non-int/non-string key
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/InvalidKeyTypeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\InvalidKeyTypeTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\InvalidKeyTypeTest::testOne is invalid
The key must be an integer or a string, float given

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\InvalidKeyTypeTest".

No tests executed!
