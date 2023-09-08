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

use function json_encode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsJson::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsJsonTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                json_encode(['key' => 'value']),
            ],

            [
                false,
                'Failed asserting that an empty string is valid JSON.',
                '',
            ],

            [
                false,
                'Failed asserting that a string is valid JSON (Syntax error, malformed JSON).',
                'invalid json',
            ],

            [
                false,
                'Failed asserting that an array is valid JSON.',
                [],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, mixed $actual): void
    {
        $constraint = new IsJson;

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
        $this->assertSame('is valid JSON', (new IsJson)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsJson));
    }
}
