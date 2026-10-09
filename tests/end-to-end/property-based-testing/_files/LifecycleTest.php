<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use Countable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Stack.php';

final class LifecycleTest extends TestCase
{
    private Stack $stack;

    protected function setUp(): void
    {
        $this->stack = new Stack;
    }

    #[Property]
    public function testEveryExampleHasAFreshFixture(int $value): void
    {
        $this->stack->push($value);

        $this->assertSame(1, $this->stack->size());
    }

    #[Property]
    public function testExpectationsForTestDoublesAreVerifiedForEveryExample(int $value): void
    {
        $countable = $this->createMock(Countable::class);

        $countable->expects($this->once())->method('count')->willReturn($value);

        $this->assertSame($value, $countable->count());
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testExpectedExceptionIsExpectedForEveryExample(int $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        if ($value % 2 === 0) {
            throw new InvalidArgumentException('even');
        }
    }
}
