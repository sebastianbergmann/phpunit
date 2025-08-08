--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6294
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare(PHP_VERSION, '8.5.0-dev', '>=')) {
    print 'skip: PHP < 8.5 is required.';
}
--INI--
display_errors=1
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6294/IssueTest6294.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


Fatal error: Access level to PHPUnit\TestFixture\B::someFunction() must be public (as in class PHPUnit\TestFixture\A) in %sB.php on line %i
