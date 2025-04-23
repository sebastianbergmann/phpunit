--TEST--
phpunit --configuration ../_files/status/phpunit.xml --log-otr php://stdout
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/status/phpunit.xml';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = 'php://stdout';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:git="https://schemas.opentest4j.org/reporting/git/0.2.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <git:repository originUrl="%s"/>
  <git:branch>%s</git:branch>
  <git:commit>%s</git:commit>
  <git:status clean="%s"><![CDATA[%A]]></git:status>
 </infrastructure>
 <e:started id="1" name="%sphpunit.xml" time="%s"/>
 <e:started id="2" name="default" time="%s"/>
 <e:started id="3" name="PHPUnit\TestFixture\Basic\StatusTest" time="%s"/>
 <e:started id="4" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testSuccess" time="%s"/>
 <e:finished id="4" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="5" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testFailure" time="%s"/>
 <e:finished id="5" time="%s">
  <result status="FAILED"/>
 </e:finished>
 <e:started id="6" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testError" time="%s"/>
 <e:finished id="6" time="%s">
  <result status="ERRORED"/>
 </e:finished>
 <e:started id="7" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testIncomplete" time="%s"/>
 <e:finished id="7" time="%s">
  <result status="ABORTED"/>
 </e:finished>
 <e:started id="8" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testSkipped" time="%s"/>
 <e:finished id="8" time="%s">
  <result status="SKIPPED"/>
 </e:finished>
 <e:started id="9" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testRisky" time="%s"/>
 <e:finished id="9" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="10" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testSuccessWithMessage" time="%s"/>
 <e:finished id="10" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="11" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testFailureWithMessage" time="%s"/>
 <e:finished id="11" time="%s">
  <result status="FAILED"/>
 </e:finished>
 <e:started id="12" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testErrorWithMessage" time="%s"/>
 <e:finished id="12" time="%s">
  <result status="ERRORED"/>
 </e:finished>
 <e:started id="13" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testIncompleteWithMessage" time="%s"/>
 <e:finished id="13" time="%s">
  <result status="ABORTED"/>
 </e:finished>
 <e:started id="14" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testSkippedWithMessage" time="%s"/>
 <e:finished id="14" time="%s">
  <result status="SKIPPED"/>
 </e:finished>
 <e:started id="15" parentId="3" name="PHPUnit\TestFixture\Basic\StatusTest::testRiskyWithMessage" time="%s"/>
 <e:finished id="15" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="3" time="%s"/>
 <e:finished id="2" time="%s"/>
 <e:finished id="1" time="%s"/>
</e:events>
