--TEST--
phpunit --configuration ../_files/baseline/generate-baseline-suppressed/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);
$baseline = __DIR__ . '/../_files/baseline/generate-baseline-suppressed/baseline.xml';
@touch($baseline);
$baseline = realpath($baseline);
@unlink($baseline);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = $baseline;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline-suppressed/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($baseline);

@unlink($baseline);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sTest.php:%d
deprecation

Triggered by:

* PHPUnit\TestFixture\Baseline\Test::testUserErrors
  %sTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Warnings: 1, Deprecations: 1, Notices: 1.

Baseline written to %sbaseline.xml.
<?xml version="1.0"?>
<files version="1">
 <file path="tests/Test.php">
  <line number="19" hash="d830ec28e59b9f13d697e91ea3944db52a4aa5c8">
   <issue><![CDATA[deprecation]]></issue>
  </line>
  <line number="20" hash="13b1892be6e70462a631716e4a730a64ba4d0c1b">
   <issue><![CDATA[warn]]></issue>
  </line>
  <line number="21" hash="fff8be75c2fbcbc4d395247e58fbbe6541189cf0">
   <issue><![CDATA[notice]]></issue>
  </line>
 </file>
</files>
