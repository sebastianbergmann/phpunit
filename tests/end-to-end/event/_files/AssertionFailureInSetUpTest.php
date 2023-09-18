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

final class AssertionFailureInSetUpTest extends TestCase
{
    #[Before]
    public function beforeTest(): void
    {
        $this->assertTrue(false);
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
