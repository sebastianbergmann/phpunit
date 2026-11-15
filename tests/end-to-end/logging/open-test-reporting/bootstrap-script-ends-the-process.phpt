--TEST--
The logfile of a test run that a bootstrap script ends says what was run, but not how the test run ended
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap-that-ends-the-process.php';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

register_shutdown_function(
    static function () use ($logfile): void
    {
        print file_get_contents($logfile);

        unlink($logfile);
    },
);

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
<?xml version="1.0"?>
<e:events xmlns="https://schemas.opentest4j.org/reporting/core/0.2.0" xmlns:e="https://schemas.opentest4j.org/reporting/events/0.2.0" xmlns:php="https://schema.phpunit.de/otr/php/0.1.0" xmlns:phpunit="https://schema.phpunit.de/otr/phpunit/0.4.0">
 <infrastructure>
  <hostName>%s</hostName>
  <userName>%s</userName>
  <operatingSystem>%s</operatingSystem>
  <php:phpVersion>%s</php:phpVersion>
  <php:threadModel>%s</php:threadModel>
  <phpunit:randomSeed>%d</phpunit:randomSeed>
  <phpunit:invocation version="%s" workingDirectory="%s" time="%s">
   <phpunit:argument>--do-not-record-test-run-history</phpunit:argument>
   <phpunit:argument>--no-configuration</phpunit:argument>
   <phpunit:argument>--no-output</phpunit:argument>
   <phpunit:argument>--bootstrap</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
   <phpunit:argument>--log-otr</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
   <phpunit:argument>%s</phpunit:argument>
   <phpunit:bootstrap path="%sbootstrap-that-ends-the-process.php"/>
  </phpunit:invocation>
 </infrastructure>
 <e:started id="0" name="PHPUnit" time="%s"/>
