--TEST--
A second PHPT test that opts out of parallel execution with a --CONFLICTS-- section listing "all"
--CONFLICTS--
all
--FILE--
<?php declare(strict_types=1);
print 'the other conflicting phpt test ran';
--EXPECT--
the other conflicting phpt test ran
