--TEST--
phpunit --log-otr /path/to/logfile ../../event/_files/AssertionFailureInTearDownAfterClassTest.php
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/AssertionFailureInTearDownAfterClassTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);
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
 <e:started id="1" name="PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest" time="%s"/>
 <e:started id="2" parentId="1" name="PHPUnit\TestFixture\Event\AssertionFailureInTearDownAfterClassTest::testOne" time="%s"/>
 <e:finished id="2" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="1" time="%s">
  <result status="FAILED"/>
 </e:finished>
</e:events>
