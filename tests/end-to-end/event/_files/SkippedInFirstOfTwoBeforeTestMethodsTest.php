<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

final class SkippedInFirstOfTwoBeforeTestMethodsTest extends TestCase
{
    #[Before(priority: 2)]
    public function beforeTestOne(): void
    {
        $this->markTestSkipped('message');
    }

    #[Before(priority: 1)]
    public function beforeTestTwo(): void
    {
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
