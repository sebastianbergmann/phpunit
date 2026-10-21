--TEST--
Two methods registered with #[GeneratorFor] for the same type are reported as an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/DuplicateGeneratorForTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\PropertyBasedTesting\DuplicateGeneratorForTest::testOne
Property PHPUnit\TestFixture\PropertyBasedTesting\DuplicateGeneratorForTest::testOne cannot be checked: PHPUnit\TestFixture\PropertyBasedTesting\DuplicateGeneratorForTest::first() and PHPUnit\TestFixture\PropertyBasedTesting\DuplicateGeneratorForTest::second() are both registered with #[GeneratorFor] for PHPUnit\TestFixture\PropertyBasedTesting\EmailAddress

%sDuplicateGeneratorForTest.php:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\PropertyBasedTesting\DuplicateGeneratorForTest".

No tests executed!
