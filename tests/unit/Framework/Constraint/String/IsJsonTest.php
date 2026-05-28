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
use function str_repeat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsJson::class)]
#[CoversClass(Constraint::class)]
#[Small]
#[Group('framework')]
#[Group('framework/constraints')]
final class IsJsonTest extends TestCase
{
    /**
     * @return non-empty-list<array{bool, string, mixed}>
     */
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

            [
                false,
                'Failed asserting that a string is valid JSON (Maximum stack depth exceeded).',
                str_repeat('[', 1000) . '1' . str_repeat(']', 1000),
            ],

            [
                false,
                'Failed asserting that a string is valid JSON (Unexpected control character found).',
                "\"\x01\"",
            ],

            [
                false,
                'Failed asserting that a string is valid JSON (Malformed UTF-8 characters, possibly incorrectly encoded).',
                "\"\xC3\x28\"",
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
        $this->expectExceptionMessageIs($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is valid JSON', (new IsJson)->toString());
    }

    public function testCanBeNegated(): void
    {
        $constraint = new LogicalNot(new IsJson);

        $this->assertSame('is not valid JSON', $constraint->toString());

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIs('Failed asserting that a string is not valid JSON.');

        $constraint->evaluate('{}');
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsJson));
    }
}
