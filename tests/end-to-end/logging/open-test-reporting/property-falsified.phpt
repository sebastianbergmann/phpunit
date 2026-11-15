--TEST--
Open Test Reporting XML logging includes how a property was falsified, with its minimal counterexample, in the reason for the failure, and the seed of the test run
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../../property-based-testing/_files/FalsifiedPropertyTest.php';

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
  <phpunit:randomSeed>1234</phpunit:randomSeed>
  <phpunit:invocation version="%s" workingDirectory="%s" time="%s">
   <phpunit:argument>--do-not-record-test-run-history</phpunit:argument>
   <phpunit:argument>--no-configuration</phpunit:argument>
   <phpunit:argument>--random-seed</phpunit:argument>
   <phpunit:argument>1234</phpunit:argument>
   <phpunit:argument>--no-output</phpunit:argument>
   <phpunit:argument>--log-otr</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
  </phpunit:invocation>
 </infrastructure>
 <e:started id="0" name="PHPUnit" time="%s"/>
 <e:started id="1" parentId="0" name="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Falsified Property (PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedProperty)"/>
  </metadata>
  <sources>
   <fileSource path="%sFalsifiedPropertyTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen" time="%s"/>
 <e:started id="3" parentId="2" name="testSumIsAtMostTen" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Sum is at most ten"/>
  </metadata>
  <sources>
   <fileSource path="%sFalsifiedPropertyTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest" methodName="testSumIsAtMostTen"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="2"/>
  </attachments>
  <result status="FAILED">
   <reason>Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after 1 example, counterexample shrunk in 2 steps:

    $a = 0;
    $b = 11;</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[Failed asserting that 11 is equal to 10 or is less than 10.

Property falsified after 1 example, counterexample shrunk in 2 steps:

    $a = 0;
    $b = 11;

%sFalsifiedPropertyTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
 </e:finished>
 <e:finished id="1" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
 </e:finished>
 <e:finished id="0" time="%s">
  <attachments>
   <phpunit:outcome status="completed" shellExitCode="1" time="%s">
    <phpunit:tests run="1" assertions="2" errored="0" failed="1" skipped="0" incomplete="0" risky="0"/>
    <phpunit:issuesTriggeredByTests errors="0" deprecations="0" notices="0" warnings="0" phpunitErrors="0" phpunitDeprecations="0" phpunitNotices="0" phpunitWarnings="0"/>
    <phpunit:issuesTriggeredByTestRunner errors="0" deprecations="0" notices="0" warnings="0" phpunitErrors="0" phpunitDeprecations="0" phpunitNotices="0" phpunitWarnings="0"/>
   </phpunit:outcome>
  </attachments>
 </e:finished>
</e:events>
