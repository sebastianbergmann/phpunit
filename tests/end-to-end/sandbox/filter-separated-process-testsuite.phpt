--TEST--
Filter test must be handled by separated process tests.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testFilterSeparatedProcessTestSuiteNoSkip';
$_SERVER['argv'][] = __DIR__ . '/_files/FilterSeparatedProcessTestSuiteTest.php';

require_once __DIR__ . '/../../bootstrap.php';

$tmpDir = __DIR__ . '/_files/temp';

if (!\file_exists($tmpDir)) {
    \mkdir($tmpDir, recursive: true);
}
\file_put_contents($tmpDir . '/filter_separated_process_testsuite_count.txt', 0);

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

if (\intval(\file_get_contents($tmpDir . '/filter_separated_process_testsuite_count.txt')) !== 1){
	throw new \Exception('Invalid method call count!');
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
