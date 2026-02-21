--TEST--
phpunit --log-otr /path/to/logfile --display-errors ../_files/ErrorIssueTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--display-errors';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/ErrorIssueTest.php';

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
 <e:started id="1" name="PHPUnit\TestFixture\OpenTestReporting\ErrorIssueTest" time="%s">
  <sources>
   <fileSource path="%sErrorIssueTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\OpenTestReporting\ErrorIssueTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <sources>
   <fileSource path="%sErrorIssueTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\OpenTestReporting\ErrorIssueTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:reported id="2" time="%s">
  <attachments>
   <phpunit:issue type="php-deprecation" message="%s" file="%sErrorIssueTest.php" line="%d" suppressed="false" ignoredByBaseline="false" ignoredByTest="false" trigger="unknown" caller="unknown" callee="unknown"/>
  </attachments>
 </e:reported>
 <e:reported id="2" time="%s">
  <attachments>
   <phpunit:issue type="error" message="message" file="%sErrorIssueTest.php" line="%d" suppressed="false"/>
  </attachments>
 </e:reported>
 <e:finished id="2" time="%s">
  <result status="ERRORED">
   <reason>E_USER_ERROR was triggered</reason>
   <phpunit:throwable type="PHPUnit\Runner\ErrorException" assertionError="false"><![CDATA[E_USER_ERROR was triggered
%sErrorIssueTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:finished id="1" time="%s"/>
</e:events>
