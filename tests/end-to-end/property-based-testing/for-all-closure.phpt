--TEST--
The generator for a parameter can be specified with #[ForAll] as a closure
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.5.0', PHP_VERSION, '>')) {
    print 'skip: PHP >= 8.5 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ForAllClosureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, %d assertions)
