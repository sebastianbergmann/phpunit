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

#[CoversClass(DirectoryExists::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class DirectoryExistsTest extends TestCase
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
                __DIR__,
            ],

            [
                false,
                'Failed asserting that directory "/does/not/exist" exists.',
                '/does/not/exist',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $actual): void
    {
        $constraint = new DirectoryExists;

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
        $this->assertSame('directory exists', (new DirectoryExists)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new DirectoryExists);

        $this->assertSame('directory does not exist', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that directory "' . __DIR__ . '" does not exist.');

        $constraint->evaluate(__DIR__);
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new DirectoryExists));
    }

    public function testMatchesReturnsFalseForNonString(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that directory "" exists.');

        (new DirectoryExists)->evaluate(123);
    }

    public function testReturnsAffirmativeStringInNonLogicalNotContext(): void
    {
        $this->assertSame(
            'directory exists',
            LogicalAnd::fromConstraints(new DirectoryExists)->toString(),
        );
    }
}
