--TEST--
TestWith: duplicate named key triggers an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/TestWithDuplicateNamedKeyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\TestWithDuplicateNamedKeyTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\TestWithDuplicateNamedKeyTest::testOne is invalid
The key "same" has already been defined by TestWith#0 attribute

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\TestWithDuplicateNamedKeyTest".

No tests executed!
