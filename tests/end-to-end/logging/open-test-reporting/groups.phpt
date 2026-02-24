--TEST--
phpunit --log-otr /path/to/logfile ../_files/GroupsTest.php
--FILE--
<?php declare(strict_types=1);
use function PHPUnit\TestFixture\validate_and_print;

$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/GroupsTest.php';

require __DIR__ . '/../../../bootstrap.php';
require __DIR__ . '/validate_and_print.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

validate_and_print($logfile);

unlink($logfile);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.1.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
 </infrastructure>
 <e:started id="1" name="PHPUnit\TestFixture\Basic\GroupsTest" time="%s">
  <sources>
   <fileSource path="%sGroupsTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:classSource className="PHPUnit\TestFixture\Basic\GroupsTest"/>
  </sources>
 </e:started>
 <e:started id="2" parentId="1" name="testWithClassAndMethodGroup" time="%s">
  <metadata>
   <tags>
    <tag>one</tag>
    <tag>two</tag>
   </tags>
  </metadata>
  <sources>
   <fileSource path="%sGroupsTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\GroupsTest" methodName="testWithClassAndMethodGroup"/>
  </sources>
 </e:started>
 <e:finished id="2" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:started id="3" parentId="1" name="testWithClassGroupOnly" time="%s">
  <metadata>
   <tags>
    <tag>one</tag>
    <tag>two</tag>
    <tag>three</tag>
   </tags>
  </metadata>
  <sources>
   <fileSource path="%sGroupsTest.php">
    <filePosition line="%d"/>
   </fileSource>
   <phpunit:methodSource className="PHPUnit\TestFixture\Basic\GroupsTest" methodName="testWithClassGroupOnly"/>
  </sources>
 </e:started>
 <e:finished id="3" time="%s">
  <result status="SUCCESSFUL"/>
 </e:finished>
 <e:finished id="1" time="%s"/>
</e:events>
