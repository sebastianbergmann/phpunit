--TEST--
PHPT runner supports XFAIL section
--FILE--
<?php declare(strict_types=1);
{syntaxError}
echo "Should not see this";
--XFAIL--
Syntax Error in PHPT is supposed to fail
--EXPECT--
Should not see this
