--TEST--
PHPUnit emits an error when a test double for a readonly class is cloned on PHP 8.2
--SKIPIF--
<?php declare(strict_types=1);
if (!version_compare('8.3.0', PHP_VERSION, '>')) {
    print 'skip: This test requires PHP 8.2' . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/clone-readonly-php-82';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Php82CloneReadonlyTestDoubleTest::testOne
PHPUnit\Framework\MockObject\CannotCloneTestDoubleForReadonlyClassException: Cloning test doubles for readonly classes is not supported on PHP 8.2

%sPhp82CloneReadonlyTestDoubleTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
