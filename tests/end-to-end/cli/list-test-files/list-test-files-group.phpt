--TEST--
phpunit --list-test-files --group one ../../_files/listing-tests-and-groups
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--list-test-files';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'one';
$_SERVER['argv'][] = __DIR__.'/../../_files/listing-tests-and-groups';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test files:
 - %slisting-tests-and-groups%sExampleTest.php
