--TEST--
phpunit --configuration ../_files/baseline/generate-baseline-with-relative-directory/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);

$baselineAbsolutePath = __DIR__ . '/../_files/baseline/generate-baseline-with-relative-directory/baseline.xml';
$baseline = ltrim(str_replace(getcwd(), '', $baselineAbsolutePath), DIRECTORY_SEPARATOR);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = $baseline;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline-with-relative-directory/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($baseline);

@unlink($baselineAbsolutePath);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.

Baseline written to %sbaseline.xml.
<?xml version="1.0"?>
<files version="1">
 <file path="tests/Test.php">
  <line number="21" hash="a1022fb62c4705938dd2c6df5ff35b2621f9e97d">
   <issue><![CDATA[deprecation]]></issue>
  </line>
 </file>
</files>
