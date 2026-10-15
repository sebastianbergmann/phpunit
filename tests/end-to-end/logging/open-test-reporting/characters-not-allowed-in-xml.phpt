--TEST--
phpunit --log-otr /path/to/logfile ../_files/CharactersNotAllowedInXmlTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../_files/CharactersNotAllowedInXmlTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.3.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
  <phpunit:randomSeed>%d</phpunit:randomSeed>
 </infrastructure>
 <e:started id="1" name="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Characters Not Allowed In Xml (PHPUnit\TestFixture\Logging\CharactersNotAllowedInXml)"/>
  </metadata>
  <sources>
   <fileSource path="%sCharactersNotAllowedInXmlTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="One"/>
  </metadata>
  <sources>
   <fileSource path="%sCharactersNotAllowedInXmlTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="FAILED">
   <reason>[31mmessage[0m</reason>
   <phpunit:throwable type="PHPUnit\Framework\AssertionFailedError" assertionError="true"><![CDATA[[31mmessage[0m

%sCharactersNotAllowedInXmlTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="3" parentId="1" name="testTwo" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Two"/>
  </metadata>
  <sources>
   <fileSource path="%sCharactersNotAllowedInXmlTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest" methodName="testTwo"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="ERRORED">
   <reason>message that is not valid UTF-8: ?</reason>
   <phpunit:throwable type="Exception" assertionError="false"><![CDATA[Exception: message that is not valid UTF-8: ?

%sCharactersNotAllowedInXmlTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="4" parentId="1" name="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest::testThree" time="%s"/>
 <e:started id="5" parentId="4" name="testThree with data set &quot;\u{0007}data set&quot;" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Three with data set &quot;\u{0007}data set&quot;"/>
  </metadata>
  <sources>
   <fileSource path="%sCharactersNotAllowedInXmlTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest" methodName="testThree"/>
  </sources>
 </e:started>
 <e:finished id="5" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="4" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
 </e:finished>
 <e:started id="6" parentId="1" name="testFour" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Four"/>
  </metadata>
  <sources>
   <fileSource path="%sCharactersNotAllowedInXmlTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\CharactersNotAllowedInXmlTest" methodName="testFour"/>
  </sources>
 </e:started>
 <e:finished id="6" time="%s">
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
