<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use const DIRECTORY_SEPARATOR;
use const E_WARNING;
use function chdir;
use function getcwd;
use function str_replace;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use ErrorException;
use Exception;
use PHPUnit\Exception as PHPUnitException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\EventCollector;
use PHPUnit\TestFixture\PropertyBased\Falsification\ExampleTest;
use PHPUnit\TestFixture\PropertyBased\Falsification\RecursionLimit;
use RuntimeException;
use Throwable;

#[CoversClass(Falsification::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class FalsificationTest extends TestCase
{
    private ?string $previousWorkingDirectory = null;
    private ?string $temporaryFile            = null;

    /**
     * @return array<string, array{non-empty-string}>
     */
    public static function falsifyingExampleProvider(): array
    {
        return [
            'failed assertion'                    => ['testFailsWithAMessageThatDependsOnItsArgument'],
            'exception'                           => ['testErrors'],
            'unmet expectation for a test double' => ['testDoesNotMeetTheExpectationForATestDouble'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string}>
     */
    public static function nonFalsifyingExampleProvider(): array
    {
        return [
            'passed'  => ['testPasses'],
            'skipped' => ['testIsSkipped'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, ?positive-int}>
     */
    public static function recursionLimitProvider(): array
    {
        return [
            'call stack limit of PHP' => [RecursionLimit::CALL_STACK_LIMIT, null],
            'nesting limit of Xdebug' => [RecursionLimit::XDEBUG_NESTING_LIMIT, 3],
        ];
    }

    protected function tearDown(): void
    {
        if ($this->previousWorkingDirectory !== null) {
            chdir($this->previousWorkingDirectory);

            $this->previousWorkingDirectory = null;
        }

        if ($this->temporaryFile !== null) {
            unlink($this->temporaryFile);

            $this->temporaryFile = null;
        }
    }

    #[DataProvider('nonFalsifyingExampleProvider')]
    public function testExampleThatNeitherFailsNorErrorsDoesNotFalsifyTheProperty(string $methodName): void
    {
        $example = $this->example($methodName, 1);

        $this->assertNull(Falsification::of($example));
        $this->assertFalse(Falsification::exampleFalsifies($example));
    }

    /**
     * @param non-empty-string $methodName
     */
    #[DataProvider('falsifyingExampleProvider')]
    public function testExampleThatFailsOrErrorsFalsifiesTheProperty(string $methodName): void
    {
        $this->assertTrue(Falsification::exampleFalsifies($this->example($methodName, 1)));
    }

    public function testExampleThatExceedsItsTimeLimitFalsifiesTheProperty(): void
    {
        $this->assertTrue(Falsification::exampleFalsifies($this->example('testExceedsItsTimeLimit', 1)));
    }

    /**
     * @param non-empty-string $methodName
     */
    #[DataProvider('falsifyingExampleProvider')]
    public function testExamplesThatEndWithTheSameThrowableAtTheSameLocationFalsifyThePropertyInTheSameWay(string $methodName): void
    {
        $first  = Falsification::of($this->example($methodName, 1));
        $second = Falsification::of($this->example($methodName, 2));

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertFalse($first->isTimeout());
        $this->assertTrue($first->isSameAs($second));
    }

    public function testExamplesThatEndWithThrowablesOfDifferentClassesFalsifyThePropertyInDifferentWays(): void
    {
        $failure = Falsification::of($this->example('testFailsWithAMessageThatDependsOnItsArgument', 1));
        $error   = Falsification::of($this->example('testErrors', 1));

        $this->assertNotNull($failure);
        $this->assertNotNull($error);
        $this->assertFalse($failure->isSameAs($error));
    }

    public function testExamplesThatEndWithThrowablesRaisedAtDifferentLocationsFalsifyThePropertyInDifferentWays(): void
    {
        $first  = Falsification::of($this->example('testErrorsAtALocationThatDependsOnItsArgument', 0));
        $second = Falsification::of($this->example('testErrorsAtALocationThatDependsOnItsArgument', 1));

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertFalse($first->isSameAs($second));
    }

    public function testExamplesThatExceedTheirTimeLimitFalsifyThePropertyInTheSameWay(): void
    {
        $first  = Falsification::of($this->example('testExceedsItsTimeLimit', 1));
        $second = Falsification::of($this->example('testExceedsItsTimeLimit', 2));
        $error  = Falsification::of($this->example('testErrors', 1));

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertNotNull($error);
        $this->assertTrue($first->isTimeout());
        $this->assertTrue($first->isSameAs($second));
        $this->assertFalse($first->isSameAs($error));
    }

    public function testDescribesHowTheExampleFalsifiedThePropertyByTheClassOfTheThrowableAndWhereItWasRaised(): void
    {
        $falsification = Falsification::of($this->example('testErrors', 1));

        $this->assertNotNull($falsification);
        $this->assertStringMatchesFormat('RuntimeException at %sExampleTest.php:%d', $falsification->description());
    }

    public function testThePathOfTheFileWhereTheThrowableWasRaisedIsRelativeToTheDirectoryOfTheConfigurationFileWhenTheFileIsBelowIt(): void
    {
        // the path does not depend on the working directory
        $this->changeWorkingDirectory(__DIR__);

        $falsification = Falsification::of($this->example('testErrors', 1));

        $this->assertNotNull($falsification);
        $this->assertStringMatchesFormat('RuntimeException at ' . str_replace('/', DIRECTORY_SEPARATOR, 'tests/_files/PropertyBased/Falsification/ExampleTest.php') . ':%d', $falsification->description());
    }

    public function testThePathOfTheFileWhereTheThrowableWasRaisedIsAbsoluteWhenTheFileIsNotBelowTheDirectoryOfTheConfigurationFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'phpunit-falsification-');

        if ($file === false) {
            $this->fail('Failed to create a temporary file.');
        }

        $this->temporaryFile = $file;

        $falsification = Falsification::ofThrowable(new ErrorException('message', 0, E_WARNING, $file, 1));

        $this->assertSame('ErrorException at ' . $file . ':1', $falsification->description());
    }

    public function testTheSignatureNamesTheClassThatTheClassOfAnAnonymousThrowableExtends(): void
    {
        $throwable = new class extends RuntimeException
        {};
        $falsification = Falsification::ofThrowable($throwable);

        $this->assertSame($throwable::class, $falsification->className());
        $this->assertStringStartsWith('RuntimeException at ', $falsification->signature()->text());
    }

    public function testTheSignatureNamesTheClassThatATestDoubleOfAThrowableExtends(): void
    {
        $throwable     = $this->createStub(RuntimeException::class);
        $falsification = Falsification::ofThrowable($throwable);

        $this->assertSame($throwable::class, $falsification->className());
        $this->assertStringStartsWith('RuntimeException at ', $falsification->signature()->text());
    }

    public function testTheSignatureNamesExceptionForATestDoubleOfAnInterfaceThatExtendsThrowable(): void
    {
        $throwable     = $this->createStub(PHPUnitException::class);
        $falsification = Falsification::ofThrowable($throwable);

        $this->assertSame($throwable::class, $falsification->className());
        $this->assertStringStartsWith(Exception::class . ' at ', $falsification->signature()->text());
    }

    public function testTheLocationOfTheErrorForTheCallStackLimitIsTheFirstOfTheFramesThatAreRepeatedInItsStackTrace(): void
    {
        // the limit is reached by the call in line 100 or by the call in line
        // 102, depending on how deep the call stack was when the recursion
        // started
        $first  = Falsification::of($this->example('testReachesTheCallStackLimit', 100));
        $second = Falsification::of($this->example('testReachesTheCallStackLimit', 102));

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertTrue($first->isSameAs($second));
        $this->assertStringEndsWith('ExampleTest.php:100', $first->description());
    }

    /**
     * @param non-empty-string $message
     * @param ?positive-int    $line
     */
    #[DataProvider('recursionLimitProvider')]
    public function testTheLocationOfTheErrorForARecursionLimitIsTheFirstFrameOfTheCycleAtTheTopOfItsStackTrace(string $message, ?int $line): void
    {
        // the finite recursion in line 10 that led to the unbounded one, whose
        // depth can depend on the example, comes first, but is not considered;
        // where the limit is reached in the cycle does not matter either
        foreach ([[20, 15, 20], [15, 20, 20], [20, 20, 15]] as $cycle) {
            foreach ([0, 1, 2, 5] as $depthOfFiniteRecursion) {
                $falsification = Falsification::ofThrowable(RecursionLimit::inRecursion($message, $cycle, $depthOfFiniteRecursion, $line));

                $this->assertStringEndsWith('RecursionLimit.php:15', $falsification->description());
            }
        }
    }

    public function testTheErrorsForTheLimitsOfDifferentRecursionsThatAreReachedThroughTheSameFiniteRecursionHaveDifferentLocations(): void
    {
        $first  = Falsification::ofThrowable(RecursionLimit::inRecursion(RecursionLimit::CALL_STACK_LIMIT, [20, 15, 20], 2));
        $second = Falsification::ofThrowable(RecursionLimit::inRecursion(RecursionLimit::CALL_STACK_LIMIT, [25], 2));

        $this->assertFalse($first->isSameAs($second));
        $this->assertStringEndsWith('RecursionLimit.php:15', $first->description());
        $this->assertStringEndsWith('RecursionLimit.php:25', $second->description());
    }

    public function testTheLocationOfTheErrorForARecursionLimitIsTheFirstRepeatedFrameWhenNoCycleRepeatsAtTheTopOfItsStackTrace(): void
    {
        $falsification = Falsification::ofThrowable(
            RecursionLimit::withStackTrace(
                RecursionLimit::CALL_STACK_LIMIT,
                [40, 41, 40, 42, 40, 43, 44, 45, 5],
                40,
            ),
        );

        $this->assertStringEndsWith('RecursionLimit.php:40', $falsification->description());
    }

    public function testTheLocationOfOtherErrorsInARecursionIsTheFirstFrameOfTheirStackTrace(): void
    {
        $first  = Falsification::of($this->example('testErrorsInARecursion', 100));
        $second = Falsification::of($this->example('testErrorsInARecursion', 102));

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertFalse($first->isSameAs($second));
        $this->assertStringEndsWith('ExampleTest.php:102', $first->description());
        $this->assertStringEndsWith('ExampleTest.php:100', $second->description());
    }

    public function testDescribesThatTheExampleFalsifiedThePropertyByExceedingTheTimeLimit(): void
    {
        $falsification = Falsification::of($this->example('testExceedsItsTimeLimit', 1));

        $this->assertNotNull($falsification);
        $this->assertSame('exceeding the time limit', $falsification->description());
    }

    private function changeWorkingDirectory(string $directory): void
    {
        $workingDirectory = getcwd();

        if ($workingDirectory === false) {
            $this->fail('Failed to capture current working directory.');
        }

        $this->previousWorkingDirectory = $workingDirectory;

        chdir($directory);
    }

    /**
     * @param non-empty-string $methodName
     */
    private function example(string $methodName, int $argument): ExampleTest
    {
        $test = new ExampleTest($methodName);

        $test->setPropertyExample(new PropertyExample([$argument], false, null));

        EventCollector::collect(
            static function () use ($test): void
            {
                try {
                    $test->runLifecycle();
                } catch (Throwable) {
                    // the lifecycle passes on the throwable that ended the
                    // test, which the assertions of these tests inspect
                    // through the test
                }
            },
        );

        return $test;
    }
}
