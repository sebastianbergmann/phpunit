--TEST--
phpunit --configuration ../_files/baseline/generate-baseline/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);
$baseline = __DIR__ . '/../_files/baseline/generate-baseline/baseline.xml';
@touch($baseline);
$baseline = realpath($baseline);
@unlink($baseline);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = $baseline;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/generate-baseline/phpunit.xml';

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
  <line number="47" hash="a1022fb62c4705938dd2c6df5ff35b2621f9e97d">
   <issue><![CDATA[deprecation]]></issue>
  </line>
  <line number="52" hash="fff8be75c2fbcbc4d395247e58fbbe6541189cf0">
   <issue><![CDATA[notice]]></issue>
  </line>
  <line number="57" hash="a5b91c0a182bedb089007e5bc0d0f462637bc904">
   <issue><![CDATA[warning]]></issue>
  </line>
  <line number="62" hash="76474d8e27ebd1f5fd11fcf0cbb60a777576df9a">
   <issue><![CDATA[Serializable@anonymous implements the Serializable interface, which is deprecated. Implement __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)]]></issue>
  </line>
  <line number="81" hash="653ed54b2a16d4fa980fde9f70c38edbab099477">
   <issue><![CDATA[Accessing static property class@anonymous::$a as non static]]></issue>
   <issue><![CDATA[Undefined property: class@anonymous::$a]]></issue>
  </line>
 </file>
</files>
