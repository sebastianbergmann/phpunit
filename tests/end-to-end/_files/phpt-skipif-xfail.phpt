--TEST--
SKIPIF section declares an expected failure at runtime
--SKIPIF--
<?php declare(strict_types=1);
print 'xfail this feature is known to be broken';
--FILE--
<?php declare(strict_types=1);
print 'actual output';
--EXPECT--
expected output
