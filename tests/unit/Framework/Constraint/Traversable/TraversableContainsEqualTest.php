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
use SplObjectStorage;
use stdClass;

#[CoversClass(TraversableContainsEqual::class)]
#[CoversClass(TraversableContains::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class TraversableContainsEqualTest extends TestCase
{
    public static function provider(): array
    {
        $o = new stdClass;

        $s = new SplObjectStorage;
        $s->attach($o);

        return [
            [
                true,
                '',
                0,
                [0],
            ],

            [
                true,
                '',
                'value',
                ['value'],
            ],

            [
                true,
                '',
                $o,
                [$o],
            ],

            [
                true,
                '',
                $o,
                $s,
            ],

            [
                true,
                '',
                '0',
                [0],
            ],

            [
                true,
                '',
                0,
                ['0'],
            ],

            [
                false,
                'Failed asserting that an array contains stdClass Object',
                $o,
                [],
            ],

            [
                false,
                'Failed asserting that a traversable contains stdClass Object',
                $o,
                new SplObjectStorage,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, mixed $expected, mixed $actual): void
    {
        $constraint = new TraversableContainsEqual($expected);

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
        $this->assertSame('contains \'value\'', (new TraversableContainsEqual('value'))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new TraversableContainsEqual('value')));
    }
}
