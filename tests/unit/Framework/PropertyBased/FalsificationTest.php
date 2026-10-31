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

use function chdir;
use function dirname;
use function getcwd;
use function realpath;
use function sys_get_temp_dir;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\EventCollector;
use PHPUnit\TestFixture\PropertyBased\Falsification\ExampleTest;
use Throwable;

#[CoversClass(Falsification::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class FalsificationTest extends TestCase
{
    private ?string $previousWorkingDirectory = null;

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

    protected function tearDown(): void
    {
        if ($this->previousWorkingDirectory !== null) {
            chdir($this->previousWorkingDirectory);

            $this->previousWorkingDirectory = null;
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

    public function testThePathOfTheFileWhereTheThrowableWasRaisedIsRelativeToTheWorkingDirectoryWhenTheFileIsBelowIt(): void
    {
        $this->changeWorkingDirectory(dirname(__DIR__, 4));

        $falsification = Falsification::of($this->example('testErrors', 1));

        $this->assertNotNull($falsification);
        $this->assertStringMatchesFormat('RuntimeException at tests/_files/PropertyBased/Falsification/ExampleTest.php:%d', $falsification->description());
    }

    public function testThePathOfTheFileWhereTheThrowableWasRaisedIsAbsoluteWhenTheFileIsNotBelowTheWorkingDirectory(): void
    {
        $this->changeWorkingDirectory(sys_get_temp_dir());

        $falsification = Falsification::of($this->example('testErrors', 1));

        $this->assertNotNull($falsification);
        $this->assertStringMatchesFormat('RuntimeException at ' . realpath(dirname(__DIR__, 4)) . '/tests/_files/PropertyBased/Falsification/ExampleTest.php:%d', $falsification->description());
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

        $test->setPropertyExample([$argument]);

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
