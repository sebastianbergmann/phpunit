--TEST--
SKIPIF section that produces unrecognized output
--SKIPIF--
<?php declare(strict_types=1);
print 'unexpected output from a broken skip check';
--FILE--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
