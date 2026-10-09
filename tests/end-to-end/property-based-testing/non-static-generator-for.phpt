--TEST--
A method registered with #[GeneratorFor] that is not public and static is reported as an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/NonStaticGeneratorForTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\PropertyBasedTesting\NonStaticGeneratorForTest::testOne
Property PHPUnit\TestFixture\PropertyBasedTesting\NonStaticGeneratorForTest::testOne cannot be checked: PHPUnit\TestFixture\PropertyBasedTesting\NonStaticGeneratorForTest::emailAddresses() is registered with #[GeneratorFor] for PHPUnit\TestFixture\PropertyBasedTesting\EmailAddress, but it is not public and static

%sNonStaticGeneratorForTest.php:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\PropertyBasedTesting\NonStaticGeneratorForTest".

No tests executed!
