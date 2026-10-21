--TEST--
A property errors when its generator throws while the minimal counterexample is generated again for the final replay
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/GeneratorThatThrowsWhenTheMinimalCounterexampleIsGeneratedAgainTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\GeneratorThatThrowsWhenTheMinimalCounterexampleIsGeneratedAgainTest::testIsFalsifiedByTheFirstExample
RuntimeException: The minimal counterexample cannot be generated again

%sGeneratorThatThrowsWhenTheMinimalCounterexampleIsGeneratedAgainTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
