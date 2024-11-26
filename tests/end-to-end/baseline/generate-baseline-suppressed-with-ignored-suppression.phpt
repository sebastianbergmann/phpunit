--TEST--
phpunit --configuration ../_files/baseline/generate-baseline-suppressed-with-ignored-suppression/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);
$baseline = __DIR__ . '/../_files/baseline/generate-baseline-suppressed-with-ignored-suppression/baseline.xml';
@touch($baseline);
$baseline = realpath($baseline);
@unlink($baseline);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = $baseline;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline-suppressed-with-ignored-suppression/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($baseline);

@unlink($baseline);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

DNWDW                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK, but there were issues!
Tests: 5, Assertions: 5, Warnings: 2, Deprecations: 2, Notices: 2.

Baseline written to %sbaseline.xml.
<?xml version="1.0"?>
<files version="1">
 <file path="src/Source.php">
  <line number="47" hash="d830ec28e59b9f13d697e91ea3944db52a4aa5c8">
   <issue><![CDATA[deprecation]]></issue>
  </line>
  <line number="52" hash="e2e96bace350722203be87b5c9990c68149e7880">
   <issue><![CDATA[notice]]></issue>
  </line>
  <line number="57" hash="53788ca16bdbb84598d44aa0efc6c022e02e9525">
   <issue><![CDATA[warning]]></issue>
  </line>
  <line number="62" hash="0c40db3a94679d19bdb71313ad8987f6a7df542f">
   <issue><![CDATA[Serializable@anonymous implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)]]></issue>
  </line>
  <line number="81" hash="2c83752a707cb31226e122e9f697a7e69773903e">
   <issue><![CDATA[Accessing static property class@anonymous::$a as non static]]></issue>
   <issue><![CDATA[Undefined property: class@anonymous::$a]]></issue>
  </line>
 </file>
</files>
