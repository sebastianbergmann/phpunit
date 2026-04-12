--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5851 - non-removable buffer does not cause infinite loop
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testNonRemovableBufferDoesNotCauseInfiniteLoop';
$_SERVER['argv'][] = __DIR__ . '/5851/Issue5851Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\Issue5851Test::testNonRemovableBufferDoesNotCauseInfiniteLoop
Test code or tested code did not close its own output buffers

%sIssue5851Test.php:%i

OK, but there were issues!
Tests: 1, Assertions: 1, Notices: 1, Risky: 1.
