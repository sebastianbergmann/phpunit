--TEST--
phpunit --log-otr php://stdout ../../event/_files/ExceptionInSetUpBeforeClassTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/ExceptionInSetUpBeforeClassTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
 </infrastructure>
 <e:started id="1" name="PHPUnit\TestFixture\Event\ExceptionInSetUpBeforeClassTest" time="%s"/>
 <e:finished id="1" time="%s">
  <result status="ERRORED"/>
 </e:finished>
</e:events>
