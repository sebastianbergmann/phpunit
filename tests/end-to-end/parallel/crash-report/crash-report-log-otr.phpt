--TEST--
phpunit --parallel=2 --log-otr logs every test of a crashed unit whose result never arrived as errored, without resource usage, like a test whose child process ended unexpectedly
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/CrashingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SteadyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/../../logging/open-test-reporting/validate_and_print.php';

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
 <e:started id="2" parentId="1" name="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Crashing (PHPUnit\TestFixture\ParallelCrashReport\Crashing)"/>
  </metadata>
  <sources>
   <fileSource path="%sCrashingTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest"/>
  </sources>
 </e:started>
 <e:started id="3" parentId="2" name="testThatPassesBeforeTheCrash" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="That passes before the crash"/>
  </metadata>
  <sources>
   <fileSource path="%sCrashingTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" methodName="testThatPassesBeforeTheCrash"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%s" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%s" systemCpuTime="%s" cpuTime="%s"/>
   <phpunit:assertions count="1"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="4" parentId="2" name="testThatKillsTheWorkerProcess" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="That kills the worker process"/>
  </metadata>
  <sources>
   <fileSource path="%sCrashingTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" methodName="testThatKillsTheWorkerProcess"/>
  </sources>
 </e:started>
 <e:finished id="4" time="%s">
  <result status="ERRORED">
   <reason>The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess.</reason>
   <phpunit:throwable type="PHPUnit\Framework\AssertionFailedError" assertionError="false"><![CDATA[The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess.
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:started id="5" parentId="2" name="testThatNeverRuns" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="That never runs"/>
  </metadata>
  <sources>
   <fileSource path="%sCrashingTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\ParallelCrashReport\CrashingTest" methodName="testThatNeverRuns"/>
  </sources>
 </e:started>
 <e:finished id="5" time="%s">
  <result status="ERRORED">
   <reason>The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess.</reason>
   <phpunit:throwable type="PHPUnit\Framework\AssertionFailedError" assertionError="false"><![CDATA[The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess.
]]></phpunit:throwable>
  </result>
 </e:finished>
 <e:finished id="2" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%s" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%s" systemCpuTime="%s" cpuTime="%s"/>
  </attachments>
 </e:finished>
 <e:started id="6" parentId="1" name="PHPUnit\TestFixture\ParallelCrashReport\SteadyTest" time="%s">
  <metadata>
   <phpunit:testDox prettifiedClassName="Steady (PHPUnit\TestFixture\ParallelCrashReport\Steady)"/>
  </metadata>
  <sources>
   <fileSource path="%sSteadyTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\ParallelCrashReport\SteadyTest"/>
  </sources>
 </e:started>
 <e:started id="7" parentId="6" name="testThatPasses" time="%s">
  <metadata>
   <phpunit:testDox prettifiedMethodName="That passes"/>
  </metadata>
  <sources>
   <fileSource path="%sSteadyTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\ParallelCrashReport\SteadyTest" methodName="testThatPasses"/>
  </sources>
 </e:started>
 <e:finished id="7" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%s" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%s" systemCpuTime="%s" cpuTime="%s"/>
   <phpunit:assertions count="1"/>
  </attachments>
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="6" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%s" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%s" systemCpuTime="%s" cpuTime="%s"/>
  </attachments>
 </e:finished>
 <e:finished id="1" time="%s">
  <attachments>
   <phpunit:resourceUsage time="%s" memoryUsage="%d" peakMemoryUsage="%d" userCpuTime="%s" systemCpuTime="%s" cpuTime="%s"/>
  </attachments>
 </e:finished>
</e:events>
