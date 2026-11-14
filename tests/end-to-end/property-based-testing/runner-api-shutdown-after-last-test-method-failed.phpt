--TEST--
The API for running examples of properties one at a time writes one line to the standard error stream, without changing the exit status, when a hook method that is called after the last test of the test class that is still prepared when the process ends raises an exception, and nothing when the test class was finished before
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
$run = static function (string $description, string ...$arguments): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/_files/runner-api-finish/run-example.php',
            ...$arguments,
        ],
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $errorOutput = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $exitStatus = proc_close($process);

    print '-- ' . $description . PHP_EOL;
    print 'standard output stream:' . PHP_EOL . $output;
    print 'standard error stream:' . PHP_EOL . $errorOutput;
    print 'exit status: ' . $exitStatus . PHP_EOL;
};

$run(
    'a hook method raises an exception when the process ends',
    'ExceptionInTearDownAfterClassTest::testPasses',
    'do-not-finish',
    '0',
);

$run(
    'a hook method raises an exception when the process ends with an exit status of the caller',
    'ExceptionInTearDownAfterClassTest::testPasses',
    'do-not-finish',
    '7',
);

$run(
    'a hook method raises an exception when the example ended the process',
    'ExitWithExceptionInTearDownAfterClassTest::testEndsTheProcess',
    'do-not-finish',
    '0',
);

$run(
    'a hook method raised an exception when the test class was finished before the process ends',
    'ExceptionInTearDownAfterClassTest::testPasses',
    'finish',
    '7',
);

$run(
    'no hook method raises an exception when the process ends',
    'FinishedTest::testPasses',
    'do-not-finish',
    '7',
);
--EXPECT--
-- a hook method raises an exception when the process ends
standard output stream:
ExceptionInTearDownAfterClassTest::setUpBeforeClass() was called
ExceptionInTearDownAfterClassTest::testPasses: passed
ExceptionInTearDownAfterClassTest::tearDownAfterClass() was called
standard error stream:
A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest that is called after its last test raised RuntimeException: cannot clean up after the test class
exit status: 0
-- a hook method raises an exception when the process ends with an exit status of the caller
standard output stream:
ExceptionInTearDownAfterClassTest::setUpBeforeClass() was called
ExceptionInTearDownAfterClassTest::testPasses: passed
ExceptionInTearDownAfterClassTest::tearDownAfterClass() was called
standard error stream:
A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest that is called after its last test raised RuntimeException: cannot clean up after the test class
exit status: 7
-- a hook method raises an exception when the example ended the process
standard output stream:
standard error stream:
A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExitWithExceptionInTearDownAfterClassTest that is called after its last test raised RuntimeException: cannot clean up after the test class
exit status: 3
-- a hook method raised an exception when the test class was finished before the process ends
standard output stream:
ExceptionInTearDownAfterClassTest::setUpBeforeClass() was called
ExceptionInTearDownAfterClassTest::testPasses: passed
ExceptionInTearDownAfterClassTest::tearDownAfterClass() was called
finish(): PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException
standard error stream:
exit status: 7
-- no hook method raises an exception when the process ends
standard output stream:
FinishedTest::setUpBeforeClass() was called, for preparation 1
FinishedTest::testPasses: passed
FinishedTest::tearDownAfterClass() was called, for preparation 1
standard error stream:
exit status: 7
