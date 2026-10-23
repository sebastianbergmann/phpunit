--TEST--
phpunit --log-otr /path/to/logfile ../_files/phpt
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt';

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
 <e:started id="1" name="CLI Arguments" time="%s"/>
 <e:started id="2" parentId="1" name="%serrored.phpt" time="%s">
  <sources>
   <fileSource path="%serrored.phpt"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
  </attachments>
  <result status="ERRORED">
   <reason>PHPUnit does not support PHPT --REDIRECTTEST-- sections</reason>
   <phpunit:throwable type="PHPUnit\Runner\Phpt\UnsupportedPhptSectionException" assertionError="false"><![CDATA[PHPUnit\Runner\Phpt\UnsupportedPhptSectionException: PHPUnit does not support PHPT --REDIRECTTEST-- sections
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="3" parentId="1" name="%sfailed.phpt" time="%s">
  <sources>
   <fileSource path="%sfailed.phpt"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="FAILED">
   <reason>Failed asserting that two strings are equal.</reason>
   <phpunit:throwable type="PHPUnit\Framework\PhptAssertionFailedError" assertionError="true"><![CDATA[Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'ok'
+'not ok'

%sfailed.phpt:%d
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="4" parentId="1" name="%sincomplete.phpt" time="%s">
  <sources>
   <fileSource path="%sincomplete.phpt"/>
  </sources>
 </e:started>
 <e:finished id="4" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="ABORTED">
   <reason>This test is expected to fail</reason>
   <phpunit:throwable type="PHPUnit\Framework\IncompleteTestError" assertionError="false"><![CDATA[This test is expected to fail

Caused by
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'ok'
+'not ok'
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="5" parentId="1" name="%sskipped.phpt" time="%s">
  <sources>
   <fileSource path="%sskipped.phpt"/>
  </sources>
 </e:started>
 <e:finished id="5" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%f" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%f" systemCpuTime="%f" cpuTime="%f"/>
   <phpunit:assertions count="%d"/>
  </attachments>
  <result status="SKIPPED">
   <reason>message</reason>
  </result>
 </e:finished>
 <e:started id="6" parentId="1" name="%ssuccessful.phpt" time="%s">
  <sources>
   <fileSource path="%ssuccessful.phpt"/>
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
