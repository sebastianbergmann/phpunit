--TEST--
PHPT test with ENV section
--ENV--
PHPUNIT_TEST_ENV_SECTION=env-section
--FILE--
<?php declare(strict_types=1);
var_dump(getenv('PHPUNIT_TEST_ENV_SECTION'));
var_dump(getenv('PHPUNIT_TEST_ENV_ELEMENT'));
var_dump(getenv('PHPUNIT_TEST_SERVER_ELEMENT'));
--EXPECT--
string(11) "env-section"
string(11) "env-element"
bool(false)
