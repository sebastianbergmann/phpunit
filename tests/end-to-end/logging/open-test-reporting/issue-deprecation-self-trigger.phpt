--TEST--
phpunit --configuration ../_files/issue-deprecation-self-trigger/phpunit.xml --log-otr /path/to/logfile --display-deprecations
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/issue-deprecation-self-trigger/phpunit.xml';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--display-deprecations';
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
 <e:started id="3" parentId="2" name="PHPUnit\TestFixture\OpenTestReporting\DeprecationSelfTriggerTest" time="%s">
  <sources>
   <fileSource path="%sDeprecationSelfTriggerTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\OpenTestReporting\DeprecationSelfTriggerTest"/>
  </sources>
 </e:started>
 <e:started id="4" parentId="3" name="testOne" time="%s">
  <sources>
   <fileSource path="%sDeprecationSelfTriggerTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\OpenTestReporting\DeprecationSelfTriggerTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:reported id="4" time="%s">
  <attachments>
   <phpunit:issue type="deprecation" message="message" file="%sDeprecationSelfTriggerTest.php" line="%d" suppressed="false" ignoredByBaseline="false" ignoredByTest="false" trigger="self" caller="PHPUnit" callee="test code"/>
  </attachments>
 </e:reported>
 <e:finished id="4" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="3" time="%s"/>
 <e:finished id="2" time="%s"/>
 <e:finished id="1" time="%s"/>
</e:events>
