--TEST--
That test impact analysis keeps no test from being run is not that every test is run when the tests are filtered as well
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-fixture-directory.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = __DIR__ . '/_files/.phpunit.cache.keeps-no-test-from-being-run';
$_SERVER['argv'][] = '--only-impacted';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'PlainTest';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv'], false);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.keeps-no-test-from-being-run');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s
Impact:        no test is kept from being run by what changed: no test impact data has been recorded

Time: %s, Memory: %s

OK (1 test, 1 assertion)
