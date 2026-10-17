--TEST--
The same seed produces the same examples for a property regardless of --filter and the execution order
--FILE--
<?php declare(strict_types=1);
function examples(string ...$arguments): array
{
    $log = tempnam(sys_get_temp_dir(), 'phpunit-property-examples-');

    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../phpunit',
            '--do-not-record-test-run-history',
            '--no-configuration',
            ...$arguments,
            __DIR__ . '/_files/ReproducibilityTest.php',
        ],
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        null,
        ['PHPUNIT_PROPERTY_EXAMPLES_LOG' => $log] + getenv(),
    );

    stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    proc_close($process);

    $examples = ['testOne' => [], 'testTwo' => []];

    foreach (file($log, FILE_IGNORE_NEW_LINES) as $line) {
        [$property, $value] = explode(': ', $line, 2);

        $examples[$property][] = $value;
    }

    unlink($log);

    return $examples;
}

$all       = examples('--random-seed', '1234');
$filtered  = examples('--random-seed', '1234', '--filter', 'testTwo');
$reversed  = examples('--random-seed', '1234', '--order-by', 'reverse');
$otherSeed = examples('--random-seed', '4321');

print 'examples checked: ' . count($all['testOne']) . ', ' . count($all['testTwo']) . PHP_EOL;
print 'same examples with --filter: ' . var_export($filtered['testTwo'] === $all['testTwo'], true) . PHP_EOL;
print 'same examples in reverse order: ' . var_export($reversed === $all, true) . PHP_EOL;
print 'same examples with another seed: ' . var_export($otherSeed === $all, true) . PHP_EOL;
--EXPECT--
examples checked: 20, 20
same examples with --filter: true
same examples in reverse order: true
same examples with another seed: false
