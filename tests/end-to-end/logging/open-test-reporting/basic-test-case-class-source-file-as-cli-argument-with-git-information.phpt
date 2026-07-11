--TEST--
phpunit --log-otr /path/to/logfile --include-git-information ../_files/StatusTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = '--include-git-information';
$_SERVER['argv'][] = __DIR__ . '/../_files/status/tests/StatusTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:git="https://schemas.opentest4j.org/reporting/git/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.2.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
  <git:repository originUrl="%s"/>
  <git:branch>%s</git:branch>
  <git:commit>%s</git:commit>
  <git:status clean="%s"><![CDATA[%A]]></git:status>
 </infrastructure>
 <e:started id="1" name="PHPUnit\TestFixture\Basic\StatusTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Test result status with and without message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Basic\StatusTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testSuccess" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Success"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSuccess"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="3" parentId="1" name="testFailure" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Failure"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testFailure"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="FAILED">
   <reason>Failed asserting that false is true.</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[Failed asserting that false is true.

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="4" parentId="1" name="testError" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Error"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testError"/>
  </sources>
 </e:started>
 <e:finished id="4" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="ERRORED">
   <reason></reason>
   <phpunit:throwable type="RuntimeException" assertionError="false"><![CDATA[RuntimeException: 

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="5" parentId="1" name="testIncomplete" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Incomplete"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testIncomplete"/>
  </sources>
 </e:started>
 <e:finished id="5" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="ABORTED">
   <reason></reason>
   <phpunit:throwable type="PHPUnit\Framework\IncompleteTestError" assertionError="false"><![CDATA[
%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="6" parentId="1" name="testSkipped" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Skipped"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSkipped"/>
  </sources>
 </e:started>
 <e:finished id="6" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SKIPPED">
   <reason></reason>
  </result>
 </e:finished>
 <e:started id="7" parentId="1" name="testRisky" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Risky"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testRisky"/>
  </sources>
 </e:started>
 <e:reported id="7" time="%s">
  <attachments>
   <phpunit:issue type="risky" message="This test did not perform any assertions"/>
  </attachments>
 </e:reported>
 <e:finished id="7" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="8" parentId="1" name="testSuccessWithMessage" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Success with message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSuccessWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="8" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="9" parentId="1" name="testFailureWithMessage" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Failure with message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testFailureWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="9" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="FAILED">
   <reason>failure with custom message
Failed asserting that false is true.</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[failure with custom message
Failed asserting that false is true.

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="10" parentId="1" name="testErrorWithMessage" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Error with message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testErrorWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="10" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="ERRORED">
   <reason>error with custom message</reason>
   <phpunit:throwable type="RuntimeException" assertionError="false"><![CDATA[RuntimeException: error with custom message

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="11" parentId="1" name="testIncompleteWithMessage" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Incomplete with message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testIncompleteWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="11" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="ABORTED">
   <reason>incomplete with custom message</reason>
   <phpunit:throwable type="PHPUnit\Framework\IncompleteTestError" assertionError="false"><![CDATA[incomplete with custom message

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="12" parentId="1" name="testSkippedByMetadata" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Skipped by metadata"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSkippedByMetadata"/>
  </sources>
 </e:started>
 <e:finished id="12" time="%s">
  <result status="SKIPPED">
   <reason>PHP &gt; 9000 is required, but PHP %s is being used.</reason>
  </result>
 </e:finished>
 <e:started id="13" parentId="1" name="testSkippedWithMessage" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Skipped with message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSkippedWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="13" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SKIPPED">
   <reason>skipped with custom message</reason>
  </result>
 </e:finished>
 <e:started id="14" parentId="1" name="testRiskyWithMessage" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Risky with message"/>
  </metadata>
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testRiskyWithMessage"/>
  </sources>
 </e:started>
 <e:reported id="14" time="%s">
  <attachments>
   <phpunit:issue type="risky" message="This test did not perform any assertions"/>
  </attachments>
 </e:reported>
 <e:finished id="14" time="%s">
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
</e:events>
