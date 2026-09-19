<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use function assert;
use function in_array;
use function is_array;
use function is_object;
use function sprintf;
use function str_starts_with;
use PHPUnit\Event;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockObjectInternal;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use SebastianBergmann\ObjectEnumerator\Enumerator;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockObjectRegistry
{
    /**
     * @var list<array{type: non-empty-string, mockObject: MockObjectInternal}>
     */
    private array $mockObjects = [];

    /**
     * @param non-empty-string $type
     */
    public function register(string $type, MockObject $mockObject): void
    {
        assert($mockObject instanceof MockObjectInternal);

        $this->mockObjects[] = [
            'type'       => $type,
            'mockObject' => $mockObject,
        ];
    }

    public function clear(): void
    {
        $this->mockObjects = [];
    }

    /**
     * @throws Throwable
     */
    public function verify(TestCase $test, Event\Emitter $emitter): void
    {
        $allowsMockObjectsWithoutExpectations = $this->allowsMockObjectsWithoutExpectations($test);
        $isPhpunitTestSuite                   = str_starts_with($test::class, 'PHPUnit\\');
        $requireSealedMockObjects             = ConfigurationRegistry::get()->requireSealedMockObjects();

        foreach ($this->mockObjects as $mockObject) {
            $mockedType = $mockObject['type'];
            $mockObject = $mockObject['mockObject'];

            if ($requireSealedMockObjects &&
                !$mockObject->__phpunit_getInvocationHandler()->isSealed()) {
                $emitter->testConsideredRisky(
                    $test->valueObjectForEvents(),
                    sprintf(
                        'Mock object for %s has not been sealed',
                        $mockedType,
                    ),
                );
            }

            if (!$mockObject->__phpunit_hasInvocationCountRule()) {
                if (!$mockObject->__phpunit_hasParametersRule() &&
                    !$mockObject->__phpunit_recordsInvocations() &&
                    !$allowsMockObjectsWithoutExpectations &&
                    !$isPhpunitTestSuite) {
                    $emitter->testTriggeredPhpunitNotice(
                        $test->valueObjectForEvents(),
                        sprintf(
                            'No expectations were configured for the mock object for %s. ' .
                            'Consider refactoring your test code to use a test stub instead. ' .
                            'The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.',
                            $mockedType,
                        ),
                    );
                }

                continue;
            }

            $test->addToAssertionCount(1);

            $mockObject->__phpunit_verify(
                $this->shouldInvocationMockerBeReset($test, $mockObject),
            );
        }
    }

    /**
     * An expectation failure raised by an invocation count rule while the
     * test method was running counts as an assertion.
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/6095
     */
    public function handleExceptionFromInvokedCountRule(TestCase $test, Throwable $t): void
    {
        if (!$t instanceof ExpectationFailedException) {
            return;
        }

        $trace = $t->getTrace();

        if (isset($trace[0]['class']) && $trace[0]['class'] === InvokedCount::class) {
            $test->addToAssertionCount(1);
        }
    }

    private function allowsMockObjectsWithoutExpectations(TestCase $test): bool
    {
        return MetadataRegistry::parser()->forClassAndMethod($test::class, $test->name())->isAllowMockObjectsWithoutExpectations()->isNotEmpty();
    }

    private function shouldInvocationMockerBeReset(TestCase $test, MockObject $mock): bool
    {
        $enumerator = new Enumerator;

        if (in_array($mock, $enumerator->enumerate($test->dependencyInput()), true)) {
            return false;
        }

        $testResult = $test->result();

        if (!is_array($testResult) && !is_object($testResult)) {
            return true;
        }

        return !in_array($mock, $enumerator->enumerate($testResult), true);
    }
}
