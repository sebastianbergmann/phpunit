--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3739 (PHP >= 8.6)
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_VERSION_ID < 80600) {
    print 'Skip: PHP >= 8.6 is required (unlink() warning message does not include path)' . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/3739/Issue3739Test.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.E                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Issue3739\Issue3739Test::testWithoutErrorSuppression
unlink(): No such file or directory

%sIssue3739Test.php:%d
%sIssue3739Test.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
