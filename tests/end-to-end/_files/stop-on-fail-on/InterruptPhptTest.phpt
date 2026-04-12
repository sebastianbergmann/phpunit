--TEST--
PHPT test that sends SIGINT to parent process
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
if (!extension_loaded('posix')) echo 'skip: Extension posix is required';
--FILE--
<?php declare(strict_types=1);
posix_kill(posix_getppid(), SIGINT);
print 'not the expected output';
--EXPECT--
this will not match
