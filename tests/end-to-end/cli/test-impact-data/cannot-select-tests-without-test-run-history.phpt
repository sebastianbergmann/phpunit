--TEST--
Only the tests that are affected by what changed cannot be run when the test run history is not recorded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-selection.xml';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--only-impacted';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.selection');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot run only the tests that are affected by what changed because the test run history is not recorded
