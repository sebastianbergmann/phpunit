--TEST--
phpunit --do-not-warn-when-php-is-not-configured-for-development (overrides warnWhenPhpIsNotConfiguredForDevelopment="true")
--INI--
display_errors=0
display_startup_errors=1
error_reporting=-1
xdebug.show_exception_trace=0
zend.assertions=1
assert.exception=1
memory_limit=-1
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/warn-when-php-is-not-configured-for-development/phpunit.xml';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--do-not-warn-when-php-is-not-configured-for-development';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
