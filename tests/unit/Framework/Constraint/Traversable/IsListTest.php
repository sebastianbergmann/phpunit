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

use function fclose;
use function fopen;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(IsList::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class IsListTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                [0 => 'value', 1 => 'another-value'],
            ],

            [
                false,
                'Failed asserting that an array is a list.',
                ['key' => 'value'],
            ],

            [
                false,
                'Failed asserting that an integer is a list.',
                0,
            ],

            [
                false,
                'Failed asserting that an instance of class stdClass is a list.',
                new stdClass,
            ],

            [
                false,
                'Failed asserting that a boolean is a list.',
                false,
            ],

            [
                false,
                'Failed asserting that a float is a list.',
                0.0,
            ],

            [
                false,
                'Failed asserting that a resource is a list.',
                fopen(__FILE__, 'r'),
            ],

            [
                false,
                'Failed asserting that a closed resource is a list.',
                self::closedResource(),
            ],

            [
                false,
                'Failed asserting that null is a list.',
                null,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, mixed $actual): void
    {
        $constraint = new IsList;

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
        $this->assertSame('is a list', (new IsList)->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new IsList));
    }

    private static function closedResource()
    {
        $resource = fopen(__FILE__, 'r');

        fclose($resource);

        return $resource;
    }
}
