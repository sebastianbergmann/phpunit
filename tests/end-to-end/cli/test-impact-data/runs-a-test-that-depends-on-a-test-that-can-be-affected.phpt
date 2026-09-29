--TEST--
A test that depends on a test that can be affected by what changed is run as well: it is given what that test returns, which is not something it executes
--FILE--
<?php declare(strict_types=1);
$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--no-progress',
        '--colors=never',
        '--configuration',
        __DIR__ . '/_files/phpunit-depends.xml',
    ],
    [1 => ['pipe', 'w']],
    $pipes,
);

$output = stream_get_contents($pipes[1]);

fclose($pipes[1]);
proc_close($process);

foreach (preg_split('/\R/', $output) as $line) {
    if (str_starts_with($line, 'OK')) {
        print $line . PHP_EOL;
    }
}

print PHP_EOL;

/*
 * The run that explains what is selected is not run in another process, so
 * that the code that selects it is covered by this test.
 */
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-depends.xml';
$_SERVER['argv'][] = '--explain-impacted';
$_SERVER['argv'][] = '--impacted-by';
$_SERVER['argv'][] = __DIR__ . '/_files/tests-with-a-test-that-depends-on-another-test/ProducingTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/.phpunit.cache.depends');
--EXPECTF--
OK (2 tests, 2 assertions)

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

2 of 2 tests can be affected by what changed.

1 test depends on something that changed:
 - PHPUnit\TestFixture\TestImpactData\ProducingTest::testProducesASum
   %sProducingTest.php

1 test depends on a test that can be affected by what changed:
 - PHPUnit\TestFixture\TestImpactData\ConsumingTest::testConsumesTheSum
