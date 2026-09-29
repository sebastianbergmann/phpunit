--TEST--
Every test can be affected by a change to what was executed outside of any test, and only the tests a data provider or a test class prepared can be affected by a change to what that data provider or test class executed
--FILE--
<?php declare(strict_types=1);
function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/outside-of-tests/phpunit.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    print $output;
}

$source = __DIR__ . '/_files/outside-of-tests/src/';

run();

print PHP_EOL . 'What the bootstrap script executed is named as changed:' . PHP_EOL . PHP_EOL;

run(['--explain-impacted', '--impacted-by', $source . 'Framework.php']);

print PHP_EOL . 'What a data provider executed is named as changed:' . PHP_EOL . PHP_EOL;

run(['--explain-impacted', '--impacted-by', $source . 'Cases.php']);

print PHP_EOL . 'What was executed before the first test of a test class is named as changed:' . PHP_EOL . PHP_EOL;

run(['--explain-impacted', '--impacted-by', $source . 'Settings.php']);

print PHP_EOL . 'Which tests depend on what the bootstrap script executed:' . PHP_EOL . PHP_EOL;

run(['--list-tests-that-depend-on', $source . 'Framework.php']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/outside-of-tests/.phpunit.cache');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

OK (7 tests, 7 assertions)

What the bootstrap script executed is named as changed:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

Every test is run: %sFramework.php was executed outside of any test

What a data provider executed is named as changed:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

2 of 7 tests can be affected by what changed.

2 tests depend on something that changed:
 - PHPUnit\TestFixture\TestImpactData\OutsideOfTests\ProvidedTest::testUsesWhatTheDataProviderProvides#0
   %sCases.php
 - PHPUnit\TestFixture\TestImpactData\OutsideOfTests\ProvidedTest::testUsesWhatTheDataProviderProvides#1
   %sCases.php


What was executed before the first test of a test class is named as changed:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

1 of 7 tests can be affected by what changed.

1 test depends on something that changed:
 - PHPUnit\TestFixture\TestImpactData\OutsideOfTests\BeforeClassTest::testReadsWhatWasSetUpBeforeTheFirstTest
   %sSettings.php


Which tests depend on what the bootstrap script executed:

PHPUnit %s by Sebastian Bergmann and contributors.

Recorded at %s from what the tests executed.

Every test depends on %sFramework.php: it was executed outside of any test
