--TEST--
phpunit --list-test-files --exclude-group group ../../../_files/Metadata/Attribute/tests/GroupTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--list-test-files';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'group';
$_SERVER['argv'][] = __DIR__.'/../../../_files/Metadata/Attribute/tests/GroupTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test files:
