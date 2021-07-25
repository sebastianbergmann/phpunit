--TEST--
phpunit --covers 'PHPUnit\TestFixture\AnnotationFilter'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/phpunit.xml';
$_SERVER['argv'][] = '--covers';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\AnnotationFilter';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\AnnotationFilterTest::testOne' started
Test 'PHPUnit\TestFixture\AnnotationFilterTest::testOne' ended


Time: %s, Memory: %s

OK (1 test, 1 assertion)
