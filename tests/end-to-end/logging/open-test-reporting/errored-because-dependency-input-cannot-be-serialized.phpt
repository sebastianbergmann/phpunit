--TEST--
phpunit --log-otr /path/to/logfile ../../event/_files/DependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/DependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php';

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
 <e:started id="1" name="PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Dependency Input Of Test In Separate Process Cannot Be Serialized (PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerialized)"/>
  </metadata>
  <sources>
   <fileSource path="%sDependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="One"/>
  </metadata>
  <sources>
   <fileSource path="%sDependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="1"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="3" parentId="1" name="testTwo" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Two"/>
  </metadata>
  <sources>
   <fileSource path="%sDependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest" methodName="testTwo"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <result status="ERRORED">
   <reason>Test could not be run in child process because a value returned by a test it depends on could not be serialized: Serialization of 'Closure' is not allowed</reason>
   <phpunit:throwable type="PHPUnit\Framework\AssertionFailedError" assertionError="false"><![CDATA[Test could not be run in child process because a value returned by a test it depends on could not be serialized: Serialization of 'Closure' is not allowed
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="4" parentId="1" name="testThree" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Three"/>
  </metadata>
  <sources>
   <fileSource path="%sDependencyInputOfTestInSeparateProcessCannotBeSerializedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Event\DependencyInputOfTestInSeparateProcessCannotBeSerializedTest" methodName="testThree"/>
  </sources>
 </e:started>
 <e:finished id="4" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="1"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="1" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
 </e:finished>
</e:events>
