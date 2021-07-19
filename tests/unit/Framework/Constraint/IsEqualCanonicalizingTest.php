<?php

declare(strict_types=1);
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
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;

/**
 * @small
 */
final class IsEqualCanonicalizingTest extends ConstraintTestCase
{
    public function testIsEqualCanonicalizingIfValuesAreIdentic(): void
    {
        $expected = 'Expected string';

        $constraint = new IsEqualCanonicalizing($expected);

        $this->assertTrue($constraint->evaluate($expected, '', true));
        $this->assertCount(1, $constraint);
    }

    public function testIsEqualCanonicalizingIfValuesAreNotIdenticAndComparatorFactoryFindsNoSuitableComparator(): void
    {
        $constraint = new IsEqualCanonicalizing('Expected string');

        $this->assertFalse($constraint->evaluate('Unexpected string', '', true));
    }

    public function testValuesAreConsideredEqualIfComparatorReturnsNull(): void
    {
        $expected = 'Expected';
        $actual   = 'Faked to be identic to expected';

        $this->setFakeComparator($expected, $actual, false);

        $constraint = new IsEqualCanonicalizing($expected);

        $this->assertTrue($constraint->evaluate($actual, '', true));
    }

    public function testValuesAreConsideredNotEqualIfComparatorThrowsException(): void
    {
        $expected = 'Expected';
        $actual   = 'Faked to be identic to expected';

        $this->setFakeComparator($expected, $actual, true);

        $constraint = new IsEqualCanonicalizing($expected);

        $this->assertFalse($constraint->evaluate($actual, '', true));
    }

    public function testExceptionIsThrownIfValuesAreNotEqual(): void
    {
        $constraint = new IsEqualCanonicalizing('Expected string');

        try {
            $constraint->evaluate('Unexpected string');

            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                'Failed asserting that two strings are equal.',
                $e->getMessage()
            );
        }
    }

    protected function setFakeComparator($expected, $actual, bool $throwException): void
    {
        $comparator = $this->createMock(Comparator::class);

        $comparator->method('setFactory')->willReturn(null);

        $comparator->expects($this->once())
            ->method('accepts')
            ->with($expected, $actual)
            ->willReturn(true);

        if ($throwException) {
            $exception = new ComparisonFailure($expected, $actual, $expected, $actual);

            $comparator->method('assertEquals')
                ->will($this->throwException($exception));
        } else {
            $comparator->expects($this->once())
                ->method('assertEquals')
                ->with($expected, $actual);
        }

        ComparatorFactory::getInstance()->register($comparator);
    }
}
