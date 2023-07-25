--TEST--
phpunit --configuration ../_files/baseline/generate-baseline/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);
$baseline = __DIR__ . '/../_files/baseline/generate-baseline/baseline.xml';

@unlink($baseline);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

var_dump(file_exists($baseline));

@unlink($baseline);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %s/Test.php:%d
deprecation

Triggered by PHPUnit\TestFixture\Baseline\Test::testOne
%s/Test.php:%d

OK, but there are issues!
Tests: 1, Assertions: 1, Deprecations: 1.

Baseline written to %s/baseline.xml.
bool(true)
