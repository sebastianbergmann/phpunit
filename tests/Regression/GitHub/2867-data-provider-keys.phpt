--TEST--
GH-2867: test names show numeric datasource only for iterators as dataprovider
GH-2639: Second `yield from` is not called from a data provider
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][1] = '--debug';
$_SERVER['argv'][2] = 'Issue2867Test';
$_SERVER['argv'][3] = __DIR__ . '/2867/Issue2867Test.php';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:%s
Configuration: %s

Test 'Issue2867Test::testArrayProvider with data set "xyx" (11, 111)' started
%s
Test 'Issue2867Test::testIteratorProvider with data set "bla" (1, 11)' started
%s
Test 'Issue2867Test::testMultipleIteratorProviders with data set "bla" (1, 11)' started
%s
Test 'Issue2867Test::testMultipleIteratorProviders with data set #0 (22, 2)' started
%s
Test 'Issue2867Test::testMultipleIteratorProviders with data set "xyx" (11, 111)' started
%s
Test 'Issue2867Test::testMultipleIteratorProviders with data set #1 (23, 31)' started
%s
Test 'Issue2867Test::testMultipleIteratorProviders with data set #2 (24, 44)' started
%s


Time: %s, Memory: %s

OK (7 tests, 7 assertions)
