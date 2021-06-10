--TEST--
phpunit --covers 'PHPUnit\TestFixture\AnnotationFilter'
--FILE--
<?php declare(strict_types=1);
// @todo Refactor this test to not rely on --debug
define('PHPUNIT_TESTSUITE', true);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/phpunit.xml';
$_SERVER['argv'][] = '--covers';
$_SERVER['argv'][] = 'PHPUnit\TestFixture\AnnotationFilter';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\AnnotationFilterTest::testOne' started
Test 'PHPUnit\TestFixture\AnnotationFilterTest::testOne' ended


Time: %s, Memory: %s

OK (1 test, 1 assertion)
