--TEST--
phpunit --log-otr /path/to/logfile ../_files/TestDoxAttributesTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/TestDoxAttributesTest.php';

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
 <e:started id="1" name="PHPUnit\TestFixture\OpenTestReporting\TestDoxAttributesTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Custom class label"/>
  </metadata>
  <sources>
   <fileSource path="%sTestDoxAttributesTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\OpenTestReporting\TestDoxAttributesTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="custom method label"/>
  </metadata>
  <sources>
   <fileSource path="%sTestDoxAttributesTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\OpenTestReporting\TestDoxAttributesTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="3" parentId="1" name="testTwo" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Two"/>
  </metadata>
  <sources>
   <fileSource path="%sTestDoxAttributesTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\OpenTestReporting\TestDoxAttributesTest" methodName="testTwo"/>
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
</e:events>
