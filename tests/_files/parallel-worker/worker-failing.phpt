--TEST--
A PHPT test that always fails, for the tests of the repeated and retried PHPT units
--FILE--
<?php declare(strict_types=1);
print 'the phpt test failed in a worker';
--EXPECT--
the phpt test ran in a worker
