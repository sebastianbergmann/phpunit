<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Exporter\Exporter;
use stdClass;

/**
 * @small
 */
final class ConstraintTest extends ConstraintTestCase
{
    public static function getDummyConstraintInstance(): Constraint
    {
        return new class extends Constraint
        {
            final public function toString(): string
            {
                return 'is ok';
            }

            final protected function matches($other): bool
            {
                return parent::matches($other);
            }

            final protected function exporter(): Exporter
            {
                return parent::exporter();
            }

            final protected function reduce(): Constraint
            {
                return parent::reduce();
            }

            final protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
            {
                parent::fail($other, $description, $comparisonFailure);
            }

            final protected function additionalFailureDescription($other): string
            {
                return parent::additionalFailureDescription($other);
            }

            final protected function failureDescription($other): string
            {
                return parent::failureDescription($other);
            }

            final protected function toStringInContext(Operator $operator, $role): string
            {
                return parent::toStringInContext($operator, $role);
            }

            final protected function failureDescriptionInContext(Operator $operator, $role, $other): string
            {
                return parent::failureDescriptionInContext($operator, $role, $other);
            }

            final public function exposedMatches($other): bool
            {
                return $this->matches($other);
            }

            final public function exposedReduce(): Constraint
            {
                return $this->reduce();
            }

            final public function exposedExporter(): Exporter
            {
                return $this->exporter();
            }

            final public function exposedFail($other, $description, ComparisonFailure $comparisonFailure = null): void
            {
                $this->fail($other, $description, $comparisonFailure);
            }

            final public function exposedAdditionalFailureDescription($other): string
            {
                return $this->additionalFailureDescription($other);
            }

            final public function exposedFailureDescription($other): string
            {
                return $this->failureDescription($other);
            }

            final public function exposedToStringInContext(Operator $operator, $role): string
            {
                return $this->toStringInContext($operator, $role);
            }

            final public function exposedFailureDescriptionInContext(Operator $operator, $role, $other): string
            {
                return $this->failureDescriptionInContext($operator, $role, $other);
            }
        };
    }

    public function testEvaluateReturnsFalse(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->assertFalse($constraint->evaluate('whatever', '', true));
        $this->assertFalse($constraint->evaluate(null, '', true));
        $this->assertFalse($constraint->evaluate(new stdClass, '', true));
    }

    public function testEvaluateFailsWithExpectationFailedException(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Failed asserting that 'whatever' is ok");

        $constraint->evaluate('whatever', '');
    }

    public function testEvaluateFailsWithExpectationFailedExceptionAndCustomMessage(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Failed asserting that 'whatever' is fine");

        $constraint->evaluate('whatever', "Failed asserting that 'whatever' is fine");
    }

    public function testCountIsOne(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->assertCount(1, $constraint);
    }

    public function testExporterReturnsMemoizedExporter(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $exporter = $constraint->exposedExporter();
        $this->assertInstanceOf(Exporter::class, $exporter);
        $this->assertSame($exporter, $constraint->exposedExporter());
    }

    public function testMatchesReturnsFalse(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->assertFalse($constraint->exposedMatches('whatever'));
        $this->assertFalse($constraint->exposedMatches(null));
        $this->assertFalse($constraint->exposedMatches(true));
        $this->assertFalse($constraint->exposedMatches(new StdClass));
    }

    public function testFailThrowsExpectationFailureExceptionWithDefaultMessage(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Failed asserting that 'whatever' is ok");

        $constraint->exposedFail('whatever', '');
    }

    public function testFailThrowsExpectationFailureExceptionWithCustomMessage(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Custom message.\nFailed asserting that 'whatever' is ok");

        $constraint->exposedFail('whatever', 'Custom message.');
    }

    public function testAdditionalFailureDescriptionReturnsEmptyString(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->assertSame('', $constraint->exposedAdditionalFailureDescription('whatever'));
    }

    public function testFailureDescriptionReturnsString(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->assertSame("'whatever' is ok", $constraint->exposedFailureDescription('whatever'));
    }

    public function testToStringInContextReturnsEmptyString(): void
    {
        $constraint = $this->getDummyConstraintInstance();
        $operator   = $this->getMockBuilder(Operator::class)->getMockForAbstractClass();

        $this->assertSame('', $constraint->exposedToStringInContext($operator, 0));
    }

    public function testFailureDescriptionInContextReturnsEmptyString(): void
    {
        $constraint = $this->getDummyConstraintInstance();
        $operator   = $this->getMockBuilder(Operator::class)->getMockForAbstractClass();

        $this->assertSame('', $constraint->exposedFailureDescriptionInContext($operator, 0, 'whatever'));
    }

    public function testReduceReturnsThis(): void
    {
        $constraint = $this->getDummyConstraintInstance();

        $this->assertSame($constraint, $constraint->exposedReduce());
    }
}
