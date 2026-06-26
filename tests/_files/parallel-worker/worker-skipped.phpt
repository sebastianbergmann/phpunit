--TEST--
A PHPT test that is skipped in-process, without running a child process
--SKIPIF--
<?php declare(strict_types=1);
print 'skip this PHPT runs entirely in the main process';
--FILE--
<?php declare(strict_types=1);
print 'this must not run';
--EXPECT--
this must not run
