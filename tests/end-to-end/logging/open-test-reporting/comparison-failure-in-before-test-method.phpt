--TEST--
phpunit --log-otr /path/to/logfile ../_files/ComparisonFailureInBeforeTestMethodTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/ComparisonFailureInBeforeTestMethodTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.5.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
  <phpunit:randomSeed>%d</phpunit:randomSeed>
  <phpunit:invocation version="%s" workingDirectory="%s" time="%s">
   <phpunit:argument>--do-not-record-test-run-history</phpunit:argument>
   <phpunit:argument>--no-configuration</phpunit:argument>
   <phpunit:argument>--no-output</phpunit:argument>
   <phpunit:argument>--log-otr</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
  </phpunit:invocation>
 </infrastructure>
 <e:started id="0" name="PHPUnit" time="%s"/>
 <e:started id="1" parentId="0" name="PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureInBeforeTestMethodTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Comparison Failure In Before Test Method (PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureInBeforeTestMethod)"/>
  </metadata>
  <sources>
   <fileSource path="%sComparisonFailureInBeforeTestMethodTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureInBeforeTestMethodTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="One"/>
  </metadata>
  <sources>
   <fileSource path="%sComparisonFailureInBeforeTestMethodTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\OpenTestReporting\ComparisonFailureInBeforeTestMethodTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
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

%sComparisonFailureInBeforeTestMethodTest.php:%d
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
 <e:finished id="0" time="%s">
  <attachments>
   <phpunit:outcome status="completed" shellExitCode="1" time="%s">
    <phpunit:tests run="1" assertions="1" errored="0" failed="1" skipped="0" incomplete="0" risky="0"/>
    <phpunit:issuesTriggeredByTests errors="0" deprecations="0" notices="0" warnings="0" phpunitErrors="0" phpunitDeprecations="0" phpunitNotices="0" phpunitWarnings="0"/>
    <phpunit:issuesTriggeredByTestRunner errors="0" deprecations="0" notices="0" warnings="0" phpunitErrors="0" phpunitDeprecations="0" phpunitNotices="0" phpunitWarnings="0"/>
   </phpunit:outcome>
  </attachments>
 </e:finished>
</e:events>
