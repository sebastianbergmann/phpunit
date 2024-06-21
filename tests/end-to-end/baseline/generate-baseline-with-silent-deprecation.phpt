--TEST--
phpunit --configuration ../_files/baseline/generate-baseline-with-silent-deprecation/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);
$baseline = __DIR__ . '/../_files/baseline/generate-baseline-with-silent-deprecation/baseline.xml';
@touch($baseline);
$baseline = realpath($baseline);
@unlink($baseline);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = $baseline;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline-with-silent-deprecation/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($baseline);

@unlink($baseline);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sTest.php:%d
silent deprecation

Triggered by:

* PHPUnit\TestFixture\Baseline\Test::testOne
  %sTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.

Baseline written to %sbaseline.xml.
<?xml version="1.0"?>
<files version="1">
 <file path="tests/Test.php">
  <line number="19" hash="526daeec6229d90a6ec16463f9e1957cc269dc67">
   <issue><![CDATA[silent deprecation]]></issue>
  </line>
 </file>
</files>
