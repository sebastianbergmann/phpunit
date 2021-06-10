--TEST--
GH-873: PHPUnit suppresses exceptions thrown outside of test case function
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_MAJOR_VERSION < 7) {
    print 'skip: PHP 7 is required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/873/Issue873Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
%AException: PHPUnit suppresses exceptions thrown outside of test case function in %s:%i
Stack trace:
%a
