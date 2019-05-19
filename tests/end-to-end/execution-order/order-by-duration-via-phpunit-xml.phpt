--TEST--
phpunit --configuration=order-by-duration.phpunit.xml
--FILE--
<?php declare(strict_types=1);

$tmpResultCache = tempnam(sys_get_temp_dir(), __FILE__);
file_put_contents($tmpResultCache, file_get_contents(__DIR__ . '/_files/TestWithDifferentDurations.phpunit.result.cache.txt'));

$phpunitXmlConfig = __DIR__ . '/_files/order-by-duration.phpunit.xml';

$_SERVER['argv'][1] = '--configuration=' . $phpunitXmlConfig;
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = '--cache-result';
$_SERVER['argv'][4] = '--cache-result-file=' . $tmpResultCache;

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
