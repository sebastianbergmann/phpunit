--TEST--
Can parse --INI-- and --ENV-- sections
--INI--
cli.prompt=PHPUNIT_PROMPT
dummy.key
--ENV--
PHPUNIT_ENV=phpunit-env-section
--FILE--
<?php
echo ini_get('cli.prompt') . "\n";
echo ini_get('dummy.key') . "\n";
echo getenv('PHPUNIT_ENV') . "\n";
?>
--EXPECT--
PHPUNIT_PROMPT

phpunit-env-section
