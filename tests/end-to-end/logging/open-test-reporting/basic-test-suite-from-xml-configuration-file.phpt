--TEST--
phpunit --configuration ../_files/status/phpunit.xml --log-otr /path/to/logfile
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/status/phpunit.xml';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.0.1" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.0.1">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
 </infrastructure>
 <e:started id="1" name="%sphpunit.xml" time="%s"/>
 <e:started id="2" parentId="1" name="default" time="%s"/>
 <e:started id="3" parentId="2" name="PHPUnit\TestFixture\Basic\StatusTest" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Basic\StatusTest"/>
  </sources>
 </e:started>
 <e:started id="4" parentId="3" name="testSuccess" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSuccess"/>
  </sources>
 </e:started>
 <e:finished id="4" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="5" parentId="3" name="testFailure" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testFailure"/>
  </sources>
 </e:started>
 <e:finished id="5" time="%s">
  <result status="FAILED">
   <reason>Failed asserting that false is true.</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[Failed asserting that false is true.

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="6" parentId="3" name="testError" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testError"/>
  </sources>
 </e:started>
 <e:finished id="6" time="%s">
  <result status="ERRORED">
   <reason></reason>
   <phpunit:throwable type="RuntimeException" assertionError="false"><![CDATA[RuntimeException: 

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="7" parentId="3" name="testIncomplete" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testIncomplete"/>
  </sources>
 </e:started>
 <e:finished id="7" time="%s">
  <result status="ABORTED">
   <reason></reason>
   <phpunit:throwable type="PHPUnit\Framework\IncompleteTestError" assertionError="false"><![CDATA[
%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="8" parentId="3" name="testSkipped" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSkipped"/>
  </sources>
 </e:started>
 <e:finished id="8" time="%s">
  <result status="SKIPPED">
   <reason></reason>
  </result>
 </e:finished>
 <e:started id="9" parentId="3" name="testRisky" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testRisky"/>
  </sources>
 </e:started>
 <e:reported id="9" time="%s">
  <attachments>
   <phpunit:issue type="risky" message="This test did not perform any assertions"/>
  </attachments>
 </e:reported>
 <e:finished id="9" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="10" parentId="3" name="testSuccessWithMessage" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSuccessWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="10" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="11" parentId="3" name="testFailureWithMessage" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testFailureWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="11" time="%s">
  <result status="FAILED">
   <reason>failure with custom message
Failed asserting that false is true.</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[failure with custom message
Failed asserting that false is true.

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="12" parentId="3" name="testErrorWithMessage" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testErrorWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="12" time="%s">
  <result status="ERRORED">
   <reason>error with custom message</reason>
   <phpunit:throwable type="RuntimeException" assertionError="false"><![CDATA[RuntimeException: error with custom message

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="13" parentId="3" name="testIncompleteWithMessage" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testIncompleteWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="13" time="%s">
  <result status="ABORTED">
   <reason>incomplete with custom message</reason>
   <phpunit:throwable type="PHPUnit\Framework\IncompleteTestError" assertionError="false"><![CDATA[incomplete with custom message

%sStatusTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="14" parentId="3" name="testSkippedByMetadata" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSkippedByMetadata"/>
  </sources>
 </e:started>
 <e:finished id="14" time="%s">
  <result status="SKIPPED">
   <reason>PHP &gt; 9000 is required.</reason>
  </result>
 </e:finished>
 <e:started id="15" parentId="3" name="testSkippedWithMessage" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testSkippedWithMessage"/>
  </sources>
 </e:started>
 <e:finished id="15" time="%s">
  <result status="SKIPPED">
   <reason>skipped with custom message</reason>
  </result>
 </e:finished>
 <e:started id="16" parentId="3" name="testRiskyWithMessage" time="%s">
  <sources>
   <fileSource path="%sStatusTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\StatusTest" methodName="testRiskyWithMessage"/>
  </sources>
 </e:started>
 <e:reported id="16" time="%s">
  <attachments>
   <phpunit:issue type="risky" message="This test did not perform any assertions"/>
  </attachments>
 </e:reported>
 <e:finished id="16" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="3" time="%s"/>
 <e:finished id="2" time="%s"/>
 <e:finished id="1" time="%s"/>
</e:events>
