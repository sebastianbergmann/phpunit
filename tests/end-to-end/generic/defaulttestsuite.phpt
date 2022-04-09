--TEST--
phpunit --testdox --configuration=__DIR__.'/../_files/configuration.defaulttestsuite.xml'
--XFAIL--
TestDox logging has not been migrated to events yet.
See https://github.com/sebastianbergmann/phpunit/issues/4702 for details.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__.'/../../_files/configuration.defaulttestsuite.xml';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

Dummy Bar (PHPUnit\TestFixture\DummyBar)
 ✔ Bar equals bar

Time: %s, Memory: %s

OK (1 test, 1 assertion)
