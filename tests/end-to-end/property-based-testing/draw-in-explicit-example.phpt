--TEST--
draw() cannot be used while an explicit example of a property is checked
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/DrawInExplicitExampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\DrawInExplicitExampleTest::testDrawsAValue
PHPUnit\Framework\Exception: draw() cannot be used while an explicit example is checked

Property falsified by explicit example #0:

    $value = 1;

%sDrawInExplicitExampleTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
