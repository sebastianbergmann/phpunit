--TEST--
A PHPT test that opts out of parallel execution with a --DO_NOT_RUN_IN_PARALLEL-- section
--DO_NOT_RUN_IN_PARALLEL--
--FILE--
<?php declare(strict_types=1);
print 'the sequential phpt test ran';
--EXPECT--
the sequential phpt test ran
