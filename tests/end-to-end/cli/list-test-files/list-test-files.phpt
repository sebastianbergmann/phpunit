--TEST--
phpunit --list-test-files --configuration ../../../_files/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--list-test-files';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__.'/../../_files/basic/configuration.basic.xml';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test files:
 - %send-to-end%sSetUpBeforeClassTest.php
 - %send-to-end%sSetUpTest.php
 - %send-to-end%sStatusTest.php
 - %send-to-end%sTearDownAfterClassTest.php
