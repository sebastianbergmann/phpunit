--TEST--
phpunit --order-by=duration ./tests/end-to-end/execution-order/_files/TestWithDifferentDurations.php
--FILE--
<?php declare(strict_types=1);

$tmpResultCache = tempnam(sys_get_temp_dir(), __FILE__);
file_put_contents($tmpResultCache, file_get_contents(__DIR__ . '/_files/TestWithDifferentDurations.phpunit.result.cache.txt'));

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = '--order-by=duration';
$_SERVER['argv'][4] = '--cache-result';
$_SERVER['argv'][5] = '--cache-result-file=' . $tmpResultCache;
$_SERVER['argv'][6] = __DIR__ . '/_files/TestWithDifferentDurations.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();

unlink($tmpResultCache);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'TestWithDifferentDurations::testTwo' started
Test 'TestWithDifferentDurations::testTwo' ended
Test 'TestWithDifferentDurations::testOne' started
Test 'TestWithDifferentDurations::testOne' ended
Test 'TestWithDifferentDurations::testThree' started
Test 'TestWithDifferentDurations::testThree' ended


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
