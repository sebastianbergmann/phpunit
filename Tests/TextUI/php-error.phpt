--TEST--
phpunit PhpError ../_files/PhpError.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'PhpError';
$_SERVER['argv'][3] = dirname(__FILE__).'/../_files/PhpError.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

EEEE

Time: %i %s, Memory: %sMb

There were 4 errors:

1) PhpErrorTestCase::testError
PHP Error: Error message

%s:%i

2) PhpErrorTestCase::testNotice
Notice: Notice message

%s:%i

3) PhpErrorTestCase::testDeprecated
Deprecated: Deprecated message

%s:%i

4) PhpErrorTestCase::testWarning
Warning: Warning message

%s:%i

FAILURES!
Tests: 4, Assertions: 0, Errors: 4.
