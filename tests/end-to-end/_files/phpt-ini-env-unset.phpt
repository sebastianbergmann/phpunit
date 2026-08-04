--TEST--
{ENV:...} in the INI section skips the test when the environment variable is not set
--INI--
docref_root={ENV:PHPT_ENVIRONMENT_VARIABLE_THAT_IS_NOT_SET}
--FILE--
<?php declare(strict_types=1);
print 'this test must not run';
--EXPECT--
this test must not run
