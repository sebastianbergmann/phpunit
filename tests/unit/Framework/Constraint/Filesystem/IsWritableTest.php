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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsWritable::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsWritableTest extends TestCase
{
    /**
     * @return non-empty-list<array{bool, string, string}>
     */
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                __FILE__,
            ],

            [
                false,
                'Failed asserting that "/is/not/writable" is writable.',
                '/is/not/writable',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $actual): void
    {
        $constraint = new IsWritable;

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is writable', (new IsWritable)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsWritable);

        $this->assertSame('is not writable', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that "' . __FILE__ . '" is not writable.');

        $constraint->evaluate(__FILE__);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsWritable));
    }

    public function testMatchesReturnsFalseForNonString(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that "" is writable.');

        (new IsWritable)->evaluate(123);
    }

    public function testReturnsAffirmativeStringInNonLogicalNotContext(): void
    {
        $this->assertSame(
            'is writable',
            LogicalAnd::fromConstraints(new IsWritable)->toString(),
        );
    }
}
