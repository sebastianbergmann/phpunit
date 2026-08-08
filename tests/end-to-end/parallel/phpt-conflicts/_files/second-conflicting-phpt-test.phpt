--TEST--
The second of two PHPT tests that share the "shared-resource" conflict key
--CONFLICTS--
shared-resource
--FILE--
<?php declare(strict_types=1);
print 'the second conflicting phpt test ran';
--EXPECT--
the second conflicting phpt test ran
