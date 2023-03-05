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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\EmptyStringException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringStartsWith::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class StringStartsWithTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                'prefix',
                'prefix substring suffix',
            ],

            [
                false,
                'Failed asserting that \'substring suffix\' starts with "prefix".',
                'prefix',
                'substring suffix',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, string $actual): void
    {
        $constraint = new StringStartsWith($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('starts with "prefix"', (new StringStartsWith('prefix'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new StringStartsWith('prefix')));
    }

    public function testRejectsEmptyPrefix(): void
    {
        $this->expectException(EmptyStringException::class);

        new StringStartsWith('');
    }
}
