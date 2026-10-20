--TEST--
A PHPT test that is expected to run under parallel test execution
--FILE--
<?php declare(strict_types=1);
print 'the phpt test ran';
--EXPECT--
the phpt test ran
