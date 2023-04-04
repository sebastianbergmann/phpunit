--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5138
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/5138/';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\Issue5138Test::testOne
The data provider specified for PHPUnit\TestFixture\Issue5138Test::testOne is invalid
message

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\Issue5138Test".

No tests executed!
