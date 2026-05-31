--TEST--
phpunit --log-otr /path/to/logfile ../_files/ComparisonFailureTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/ComparisonFailureTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.2.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
 </infrastructure>
 <e:started id="1" name="PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Comparison Failure (PHPUnit\TestFixture\OpenTestReporting\ComparisonFailure)"/>
  </metadata>
  <sources>
   <fileSource path="%sComparisonFailureTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testEquals" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Equals"/>
  </metadata>
  <sources>
   <fileSource path="%sComparisonFailureTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureTest" methodName="testEquals"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="FAILED">
   <reason>Failed asserting that two arrays are identical.</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => 'a',
-    1 => 'b',
+    1 => 'x',
     2 => 'c',
 ]

%sComparisonFailureTest.php:%d
]]></phpunit:throwable>
   <phpunit:comparisonFailure>
    <phpunit:expected><![CDATA[Array &0 [
    0 => 'a',
    1 => 'b',
    2 => 'c',
]]]></phpunit:expected>
    <phpunit:actual><![CDATA[Array &0 [
    0 => 'a',
    1 => 'x',
    2 => 'c',
]]]></phpunit:actual>
    <phpunit:diff><![CDATA[
--- Expected
+++ Actual
@@ @@
 Array &0 [
     0 => 'a',
-    1 => 'b',
+    1 => 'x',
     2 => 'c',
 ]
]]></phpunit:diff>
   </phpunit:comparisonFailure>
  </result>
 </e:finished>
 <e:finished id="1" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
 </e:finished>
</e:events>
