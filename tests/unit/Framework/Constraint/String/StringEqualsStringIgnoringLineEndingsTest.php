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
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringEqualsStringIgnoringLineEndings::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class StringEqualsStringIgnoringLineEndingsTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                "string\r",
                "string\r\n",
            ],

            [
                false,
                'Failed asserting that \'another string\' is equal to "string" ignoring line endings.',
                'string',
                'another string',
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, string $actual): void
    {
        $constraint = new StringEqualsStringIgnoringLineEndings($expected);

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
        $this->assertSame('is equal to "string" ignoring line endings', (new StringEqualsStringIgnoringLineEndings('string'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new StringEqualsStringIgnoringLineEndings('string')));
    }
}
