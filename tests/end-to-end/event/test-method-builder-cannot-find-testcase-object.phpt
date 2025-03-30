--TEST--
The right exception is raised when TestMethodBuilder::fromCallStack() cannot find a TestCase object
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

\PHPUnit\Event\Code\TestMethodBuilder::fromCallStack();
--EXPECTF--
Fatal error: Uncaught PHPUnit\Event\Code\NoTestCaseObjectOnCallStackException: Cannot find TestCase object on call stack in %s:%d
Stack trace:
%a
