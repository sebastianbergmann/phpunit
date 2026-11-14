--TEST--
The API for running examples of properties one at a time raises an exception with the test class and the throwable when a hook method that is called after the last test of the test class that is finished raises an exception, fails an assertion, or marks the test as skipped, and does not finish the test class again
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

$phpunit = PropertyRunner::boot('phpunit.xml', 'TearDownAfterClassTest|AfterClassMethodTest');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\\';

foreach (
    [
        'ExceptionInTearDownAfterClassTest',
        'AssertionFailureInTearDownAfterClassTest',
        'SkippedInTearDownAfterClassTest',
        'ExceptionWithoutMessageInAfterClassMethodTest',
    ] as $testClass
) {
    print $testClass . '::testPasses: ' . $phpunit->replay($prefix . $testClass . '::testPasses', [3], 1)['outcome'] . PHP_EOL;

    try {
        $phpunit->finish();

        print 'finish() did not raise an exception' . PHP_EOL;
    } catch (AfterLastTestMethodFailedException $e) {
        print 'test class: ' . $e->className() . PHP_EOL;
        print 'message: ' . $e->getMessage() . PHP_EOL;
        print 'previous exception: ' . $e->getPrevious()::class . ' with message "' . $e->getPrevious()->getMessage() . '"' . PHP_EOL;
    }

    // the test class is finished
    $phpunit->finish();

    print PHP_EOL;
}
--EXPECT--
ExceptionInTearDownAfterClassTest::setUpBeforeClass() was called
ExceptionInTearDownAfterClassTest::testPasses: passed
ExceptionInTearDownAfterClassTest::tearDownAfterClass() was called
test class: PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest
message: A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionInTearDownAfterClassTest that is called after its last test raised RuntimeException: cannot clean up after the test class
previous exception: RuntimeException with message "cannot clean up after the test class"

AssertionFailureInTearDownAfterClassTest::testPasses: passed
test class: PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\AssertionFailureInTearDownAfterClassTest
message: A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\AssertionFailureInTearDownAfterClassTest that is called after its last test raised PHPUnit\Framework\ExpectationFailedException: was not cleaned up
Failed asserting that false is true.
previous exception: PHPUnit\Framework\ExpectationFailedException with message "was not cleaned up
Failed asserting that false is true."

SkippedInTearDownAfterClassTest::testPasses: passed
test class: PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\SkippedInTearDownAfterClassTest
message: A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\SkippedInTearDownAfterClassTest that is called after its last test raised PHPUnit\Framework\MarkedIncompleteOrSkippedInAfterHookMethodException: markTestSkipped() must not be called in after-last-test method PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\SkippedInTearDownAfterClassTest::tearDownAfterClass()
previous exception: PHPUnit\Framework\MarkedIncompleteOrSkippedInAfterHookMethodException with message "markTestSkipped() must not be called in after-last-test method PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\SkippedInTearDownAfterClassTest::tearDownAfterClass()"

ExceptionWithoutMessageInAfterClassMethodTest::testPasses: passed
test class: PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionWithoutMessageInAfterClassMethodTest
message: A hook method of PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\ExceptionWithoutMessageInAfterClassMethodTest that is called after its last test raised LogicException
previous exception: LogicException with message ""
