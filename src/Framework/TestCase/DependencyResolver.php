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

use function class_exists;
use function is_object;
use function sprintf;
use DeepCopy\DeepCopy;
use PHPUnit\Event;
use PHPUnit\Framework\ExecutionOrderDependency;
use PHPUnit\Framework\InvalidDependencyException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\TestRunner\TestResult\PassedTests;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DependencyResolver
{
    /**
     * Returns true when the test can be run.
     *
     * Returns false when the test cannot be run because one of its
     * dependencies does not exist or did not pass. The test's status is set
     * and the corresponding event is emitted in that case.
     *
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function resolve(TestCase $test, array $dependencies, Event\Emitter $emitter): bool
    {
        if ($dependencies === []) {
            return true;
        }

        $passedTests     = PassedTests::instance();
        $dependencyInput = [];

        foreach ($dependencies as $dependency) {
            if ($dependency->targetIsClass()) {
                $dependencyClassName = $dependency->getTargetClassName();

                if (!class_exists($dependencyClassName)) {
                    $this->markErrorForInvalidDependency($test, $dependency, $emitter);

                    return false;
                }

                if (!$passedTests->hasTestClassPassed($dependencyClassName)) {
                    $this->markSkippedForMissingDependency($test, $dependency, $emitter);

                    return false;
                }
            } else {
                $dependencyTarget = $dependency->getTarget();

                if (!$passedTests->hasTestMethodPassed($dependencyTarget)) {
                    if (!$dependency->targetIsCallableTestMethod()) {
                        $this->markErrorForInvalidDependency($test, $dependency, $emitter);
                    } else {
                        $this->markSkippedForMissingDependency($test, $dependency, $emitter);
                    }

                    return false;
                }

                if ($passedTests->isGreaterThan($dependencyTarget, $test->size())) {
                    $emitter->testConsideredRisky(
                        $test->valueObjectForEvents(),
                        'This test depends on a test that is larger than itself',
                    );

                    $test->setDependencyInput($dependencyInput);

                    return true;
                }

                if (!$passedTests->hasReturnValue($dependencyTarget)) {
                    $test->setDependencyInput($dependencyInput);

                    return true;
                }

                $returnValue = $passedTests->returnValue($dependencyTarget);

                if ($dependency->deepClone()) {
                    $deepCopy = new DeepCopy;
                    $deepCopy->skipUncloneable(false);

                    $dependencyInput[$dependencyTarget] = $deepCopy->copy($returnValue);
                } elseif ($dependency->shallowClone() && is_object($returnValue)) {
                    $dependencyInput[$dependencyTarget] = clone $returnValue;
                } else {
                    $dependencyInput[$dependencyTarget] = $returnValue;
                }
            }
        }

        $test->setDependencyInput($dependencyInput);

        return true;
    }

    private function markErrorForInvalidDependency(TestCase $test, ExecutionOrderDependency $dependency, Event\Emitter $emitter): void
    {
        if ($dependency->targetIsClass()) {
            $target = $dependency->getTargetClassName();
        } else {
            $target = $dependency->getTarget();
        }

        $message = sprintf(
            'This test depends on "%s" which does not exist',
            $target,
        );

        $emitter->testErrored(
            $test->valueObjectForEvents(),
            Event\Code\ThrowableBuilder::from(new InvalidDependencyException($message)),
        );

        $test->setStatus(TestStatus::error($message));
    }

    private function markSkippedForMissingDependency(TestCase $test, ExecutionOrderDependency $dependency, Event\Emitter $emitter): void
    {
        $message = sprintf(
            'This test depends on "%s" to pass',
            $dependency->getTarget(),
        );

        $emitter->testSkipped(
            $test->valueObjectForEvents(),
            $message,
        );

        $test->setStatus(TestStatus::skipped($message));
    }
}
