--TEST--
Filesystem code coverage targets that are outside of the code that is configured to be first-party code using <source> trigger a warning and are ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/filesystem-target-outside-of-source';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/filesystem-target-outside-of-source/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 4 PHPUnit warnings:

1) PHPUnit\TestFixture\FilesystemTargetOutsideOfSource\FooTest::testDoSomething
* File %stests%eFooTest.php is outside of the code that is configured to be first-party code using <source>, the attribute is ignored

* Directory %stests is outside of the code that is configured to be first-party code using <source>, the attribute is ignored

* Directory (recursively) %stests is outside of the code that is configured to be first-party code using <source>, the attribute is ignored

* Directory %stests%e.. is outside of the code that is configured to be first-party code using <source>, the attribute is ignored

%stests%eFooTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 2, PHPUnit Warnings: 1.
