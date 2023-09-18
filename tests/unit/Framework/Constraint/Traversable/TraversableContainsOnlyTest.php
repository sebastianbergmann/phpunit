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
use stdClass;

#[CoversClass(TraversableContainsOnly::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class TraversableContainsOnlyTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                'integer',
                true,
                [0, 1, 2],
            ],

            [
                true,
                '',
                stdClass::class,
                false,
                [new stdClass, new stdClass, new stdClass],
            ],

            [
                false,
                <<<'EOT'
Failed asserting that Array &0 [
    0 => 0,
    1 => '1',
    2 => 2,
] contains only values of type "integer".
EOT,
                'integer',
                true,
                [0, '1', 2],
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, string $expected, bool $isNativeType, mixed $actual): void
    {
        $constraint = new TraversableContainsOnly($expected, $isNativeType);

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
        $this->assertSame('contains only values of type "integer"', (new TraversableContainsOnly('integer'))->toString());
        $this->assertSame('contains only values of type "stdClass"', (new TraversableContainsOnly(stdClass::class, false))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new TraversableContainsOnly(stdClass::class, false)));
    }
}
