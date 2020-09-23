--TEST--
phpunit --covers 'PHPUnit\TestFixture\AnnotationFilter'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--debug';
$_SERVER['argv'][2] = '--configuration';
$_SERVER['argv'][3] = __DIR__ . '/phpunit.xml';
$_SERVER['argv'][4] = '--uses';
$_SERVER['argv'][5] = 'PHPUnit\TestFixture\AnnotationFilter';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\AnnotationFilterTest::testTwo' started
Test 'PHPUnit\TestFixture\AnnotationFilterTest::testTwo' ended


Time: %s, Memory: %s

OK (1 test, 1 assertion)
