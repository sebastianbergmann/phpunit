--TEST--
The API for running examples of properties one at a time raises an exception before an example of a property of another test class is run when a hook method that is called after the last test of the test class that was prepared last raises an exception, and prepares the other test class and runs the example, without finishing the former again, when the call is repeated
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException;
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api-finish');

$phpunit = PropertyRunner::boot('phpunit.xml', 'ExceptionInTearDownAfterClassTest|FinishedTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

$examples = [
    'replay()'   => static fn (string $property): array => $phpunit->replay($prefix . $property, [3], 1),
    'generate()' => static fn (string $property): array => $phpunit->generate($prefix . $property, 1234, 1),
];

foreach ($examples as $method => $example) {
    print '-- ' . $method . PHP_EOL;

    foreach (
        [
            'ExceptionInTearDownAfterClassTest::testPasses',
            'FinishedTest::testPasses',
            'FinishedTest::testPasses',
            'FinishedTest::testPasses',
            'OtherFinishedTest::testPasses',
        ] as $property
    ) {
        try {
            print $property . ': ' . $example($property)['outcome'] . PHP_EOL;
        } catch (AfterLastTestMethodFailedException $e) {
            print $property . ': ' . $e::class . PHP_EOL;
            print 'test class: ' . $e->className() . PHP_EOL;
            print 'message: ' . $e->getMessage() . PHP_EOL;
            print 'previous exception: ' . $e->getPrevious()::class . ' with message "' . $e->getPrevious()->getMessage() . '"' . PHP_EOL;
        }
    }

    $phpunit->finish();
}
--EXPECT--
-- replay()
ExceptionInTearDownAfterClassTest::setUpBeforeClass() was called
ExceptionInTearDownAfterClassTest::testPasses: passed
ExceptionInTearDownAfterClassTest::tearDownAfterClass() was called
FinishedTest::testPasses: PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException
test class: PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest
message: A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest that is called after its last test raised RuntimeException: cannot clean up after the test class
previous exception: RuntimeException with message "cannot clean up after the test class"
FinishedTest::setUpBeforeClass() was called, for preparation 1
FinishedTest::testPasses: passed
FinishedTest::testPasses: passed
FinishedTest::tearDownAfterClass() was called, for preparation 1
OtherFinishedTest::setUpBeforeClass() was called, for preparation 1
OtherFinishedTest::testPasses: passed
OtherFinishedTest::tearDownAfterClass() was called, for preparation 1
-- generate()
ExceptionInTearDownAfterClassTest::setUpBeforeClass() was called
ExceptionInTearDownAfterClassTest::testPasses: passed
ExceptionInTearDownAfterClassTest::tearDownAfterClass() was called
FinishedTest::testPasses: PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException
test class: PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest
message: A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest that is called after its last test raised RuntimeException: cannot clean up after the test class
previous exception: RuntimeException with message "cannot clean up after the test class"
FinishedTest::setUpBeforeClass() was called, for preparation 2
FinishedTest::testPasses: passed
FinishedTest::testPasses: passed
FinishedTest::tearDownAfterClass() was called, for preparation 2
OtherFinishedTest::setUpBeforeClass() was called, for preparation 2
OtherFinishedTest::testPasses: passed
OtherFinishedTest::tearDownAfterClass() was called, for preparation 2
