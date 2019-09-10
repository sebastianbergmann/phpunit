--TEST--
phpunit ../_files/DoesNotPerformAssertionsButPerformingAssertionsTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__ . '/../_files/DoesNotPerformAssertionsButPerformingAssertionsTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) DoesNotPerformAssertionsButPerformingAssertionsTest::testFalseAndTrueAreStillFine
This test is annotated with "@doesNotPerformAssertions" but performed 2 assertions

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 2, Risky: 1.
