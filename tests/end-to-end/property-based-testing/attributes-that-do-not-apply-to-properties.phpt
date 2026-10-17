--TEST--
#[Repeat], #[Retry], and #[RunInSeparateProcess] do not apply to a property, and a warning is emitted for them
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/IneligibleAttributesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 3 PHPUnit test runner warnings:

1) Method PHPUnit\TestFixture\PropertyBasedTesting\IneligibleAttributesTest::testIsAnnotatedWithRepeat is annotated with #[Repeat] but is a property and will not be repeated

2) Method PHPUnit\TestFixture\PropertyBasedTesting\IneligibleAttributesTest::testIsAnnotatedWithRetry is annotated with #[Retry] but is a property and will not be retried

3) Method PHPUnit\TestFixture\PropertyBasedTesting\IneligibleAttributesTest::testIsAnnotatedWithRunInSeparateProcess is annotated with #[RunInSeparateProcess] but is a property and will not be run in a separate process

OK, but there were issues!
Tests: 3, Assertions: 30, PHPUnit Warnings: 3.
