--TEST--
The first of two PHPT tests that share the "shared-resource" conflict key
--CONFLICTS--
# comments and blank lines are ignored
shared-resource

another-resource
--FILE--
<?php declare(strict_types=1);
print 'the first conflicting phpt test ran';
--EXPECT--
the first conflicting phpt test ran
