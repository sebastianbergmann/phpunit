--TEST--
XFAIL test that passes
--XFAIL--
this test is expected to fail
--FILE--
<?php declare(strict_types=1);
print '*';
--EXPECT--
*
