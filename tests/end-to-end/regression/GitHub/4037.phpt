--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4037
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../../bootstrap.php';

$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = __DIR__ . '/4037/';
$_SERVER['argv'][3] = __DIR__ . '/4037/Issue4037ATest.php';

PHPUnit\TextUI\Command::main(false);

$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = __DIR__ . '/4037/';
$_SERVER['argv'][3] = __DIR__ . '/4037/Issue4037BTest.php';

PHPUnit\TextUI\Command::main(false);

@unlink(__DIR__ . '/4037/.phpunit.result.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
