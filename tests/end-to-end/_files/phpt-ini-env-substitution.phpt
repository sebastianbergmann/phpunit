--TEST--
{ENV:...} is substituted in the INI section when the environment variable is set
--INI--
docref_root={ENV:PHPT_INI_ENV_SUBSTITUTION}
--FILE--
<?php declare(strict_types=1);
print ini_get('docref_root');
--EXPECT--
phpt-ini-env-substitution-value
