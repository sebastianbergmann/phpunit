--TEST--
{PWD} is substituted in the ENV section
--ENV--
PHPT_ENV_PWD={PWD}
--FILE--
<?php declare(strict_types=1);
var_dump(getenv('PHPT_ENV_PWD') === __DIR__);
--EXPECT--
bool(true)
