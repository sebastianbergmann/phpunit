<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use function get_class;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Test\AfterTest
 */
final class AfterTestTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $test = new Test();

        $result = new class implements Result {
            public function is(Result $other): bool
            {
                return false;
            }

            public function asString(): string
            {
                return get_class($this);
            }
        };

        $event = new AfterTest(
            $test,
            $result
        );

        $this->assertSame($test, $event->test());
        $this->assertSame($result, $event->result());
    }
}
