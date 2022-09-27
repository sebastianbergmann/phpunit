--TEST--
phpunit --no-configuration --testdox --group 3502 ../../_files/NumericGroupAnnotationTest.php
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/4702 for details.
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
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Time: %s, Memory: %s

Numeric Group Annotation (PHPUnit\TestFixture\NumericGroupAnnotation)
 ✔ Ticket annotation supports numeric value
 ✔ Group annotation supports numeric value

OK (2 tests, 2 assertions)
