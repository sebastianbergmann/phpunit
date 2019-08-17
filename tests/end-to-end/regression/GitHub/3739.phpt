--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3739
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue3739Test';
$_SERVER['argv'][3] = __DIR__ . '/3739/Issue3739Test.php';

require __DIR__ . '/../../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.E                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Issue3739\Issue3739Test::testWithoutErrorSuppression
unlink(%s/end-to-end/regression/GitHub/3739/DOES_NOT_EXIST): No such file or directory

%s/Issue3739Test.php:%d
%s/Issue3739Test.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
