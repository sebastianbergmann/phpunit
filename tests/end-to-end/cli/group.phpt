--TEST--
phpunit --no-configuration --testdox --group 3502 ../../_files/NumericGroupAnnotationTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = '3502';
$_SERVER['argv'][] = __DIR__ . '/../../_files/NumericGroupAnnotationTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Numeric Group Annotation (PHPUnit\TestFixture\NumericGroupAnnotation)
 ✔ Empty test for @ticket numeric annotation values
 ✔ Empty test for @group numeric annotation values

OK (2 tests, 2 assertions)
