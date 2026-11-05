--TEST--
A property whose requirements are not met is skipped without generating arguments for it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/_files/UnmetRequirementsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

S                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 skipped test:

1) PHPUnit\TestFixture\PropertyBasedTesting\UnmetRequirementsTest::testRequiresAnExtensionThatIsNotLoaded
PHP extension this-extension-does-not-exist is required, but it is not loaded.

OK, but some tests were skipped!
Tests: 1, Assertions: 0, Skipped: 1.
