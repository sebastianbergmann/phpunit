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

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Test\AfterTest
 */
final class AfterTestTest extends TestCase
{
    public function testTypeIsAfterTest(): void
    {
        $test = new Test();

        $event = new AfterTest($test);

        $this->assertTrue($event->type()->is(new AfterTestType()));
        $this->assertSame($test, $event->test());
    }
}
