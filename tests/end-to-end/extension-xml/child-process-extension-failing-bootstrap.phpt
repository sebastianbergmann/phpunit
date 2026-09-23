--TEST--
Test runner warning is triggered when bootstrapping an extension in the child process of a test that runs in a separate process fails
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/child-process-extension-failure/phpunit-bootstrap.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Bootstrapping of extension PHPUnit\TestFixture\ChildProcessExtension\Failure\FailingBootstrapExtension in a separate process failed: message
%A

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
