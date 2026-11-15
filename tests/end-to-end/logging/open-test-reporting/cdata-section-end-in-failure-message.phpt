--TEST--
phpunit --log-otr /path/to/logfile ../_files/CdataSectionEndInFailureMessageTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../_files/CdataSectionEndInFailureMessageTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.4.0">
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
 <e:started id="1" parentId="0" name="PHPUnit\TestFixture\Logging\CdataSectionEndInFailureMessageTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Cdata Section End In Failure Message (PHPUnit\TestFixture\Logging\CdataSectionEndInFailureMessage)"/>
  </metadata>
  <sources>
   <fileSource path="%sCdataSectionEndInFailureMessageTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Logging\CdataSectionEndInFailureMessageTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="One"/>
  </metadata>
  <sources>
   <fileSource path="%sCdataSectionEndInFailureMessageTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\CdataSectionEndInFailureMessageTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="FAILED">
   <reason>&lt;![CDATA[message]]&gt;</reason>
   <phpunit:throwable type="PHPUnit\Framework\AssertionFailedError" assertionError="true"><![CDATA[<![CDATA[message]]]]><![CDATA[>

%sCdataSectionEndInFailureMessageTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="3" parentId="1" name="testTwo" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Two"/>
  </metadata>
  <sources>
   <fileSource path="%sCdataSectionEndInFailureMessageTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\CdataSectionEndInFailureMessageTest" methodName="testTwo"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="1" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
 </e:finished>
 <e:finished id="0" time="%s">
  <attachments>
   <phpunit:outcome status="completed" shellExitCode="1" time="%s">
    <phpunit:tests run="2" assertions="2" errored="0" failed="1" skipped="0" incomplete="0" risky="0"/>
    <phpunit:issuesTriggeredByTests errors="0" deprecations="0" notices="0" warnings="0" phpunitErrors="0" phpunitDeprecations="0" phpunitNotices="0" phpunitWarnings="0"/>
    <phpunit:issuesTriggeredByTestRunner errors="0" deprecations="0" notices="0" warnings="0" phpunitErrors="0" phpunitDeprecations="0" phpunitNotices="0" phpunitWarnings="0"/>
   </phpunit:outcome>
  </attachments>
 </e:finished>
</e:events>
