--TEST--
GH-498: The test methods won't be run if a dataProvider throws Exception and --group is added in command line
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'trueOnly';
$_SERVER['argv'][] = __DIR__ . '/498/Issue498Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue498Test::shouldBeFalse
The data provider specified for PHPUnit\TestFixture\Issue498Test::shouldBeFalse is invalid
Can't create the data

%s:%d

No tests executed!
