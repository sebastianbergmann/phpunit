--TEST--
SKIPIF section that prints a keyword PHPUnit tolerates but does not act upon
--SKIPIF--
<?php declare(strict_types=1);
print 'flaky depends on network timing';
--FILE--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
