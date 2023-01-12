--TEST--
phpunit --testdox --configuration=__DIR__.'/../_files/configuration.defaulttestsuite.xml'
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__.'/../../_files/configuration.defaulttestsuite.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

Time: %s, Memory: %s

Dummy Bar (PHPUnit\TestFixture\DummyBar)
 ✔ Bar equals bar

OK (1 test, 1 assertion)
