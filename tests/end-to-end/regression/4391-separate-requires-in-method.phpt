--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4391
--INI--
disable_functions=proc_open
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/4391/RunInSeparateProcessMethodTest.php';

require_once __DIR__ . '/../../bootstrap.php';

$buffer  = \file_get_contents(__DIR__ . '/../../../src/Runner/Version.php');
$start   = \strpos($buffer, 'new VersionId(\'') + \strlen('new VersionId(\'');
$end     = \strpos($buffer, '\'', $start);
$version = \substr($buffer, $start, $end - $start);

// Version::$version requires the proc_open function
(new ReflectionProperty(PHPUnit\Runner\Version::class, 'version'))->setValue(null, $version);
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.
