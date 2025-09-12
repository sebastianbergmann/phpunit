--TEST--
#2382: Data Providers throw error with uncloneable object
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare(PHP_VERSION, '8.5', '>=')) {
    print 'skip: This test triggers a deprecation warning on PHP >= 8.5';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/2382/Issue2382Test.php';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
