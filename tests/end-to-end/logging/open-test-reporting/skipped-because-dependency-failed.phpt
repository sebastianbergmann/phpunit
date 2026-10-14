--TEST--
phpunit --log-otr /path/to/logfile ../_files/SkippedBecauseDependencyFailedTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../_files/SkippedBecauseDependencyFailedTest.php';

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
 <e:started id="1" name="PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailedTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Skipped Because Dependency Failed (PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailed)"/>
  </metadata>
  <sources>
   <fileSource path="%sSkippedBecauseDependencyFailedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailedTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testOne" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="One"/>
  </metadata>
  <sources>
   <fileSource path="%sSkippedBecauseDependencyFailedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailedTest" methodName="testOne"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="1"/>
  </attachments>
  <result status="FAILED">
   <reason>Failed asserting that false is true.</reason>
   <phpunit:throwable type="PHPUnit\Framework\ExpectationFailedException" assertionError="true"><![CDATA[Failed asserting that false is true.

%sSkippedBecauseDependencyFailedTest.php:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="3" parentId="1" name="testTwo" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Two"/>
  </metadata>
  <sources>
   <fileSource path="%sSkippedBecauseDependencyFailedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailedTest" methodName="testTwo"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <result status="SKIPPED">
   <reason>This test depends on &quot;PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailedTest::testOne&quot; to pass</reason>
  </result>
 </e:finished>
 <e:started id="4" parentId="1" name="testThree" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="Three"/>
  </metadata>
  <sources>
   <fileSource path="%sSkippedBecauseDependencyFailedTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Logging\SkippedBecauseDependencyFailedTest" methodName="testThree"/>
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
