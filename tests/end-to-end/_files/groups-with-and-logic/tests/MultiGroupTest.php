<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\GroupsWithAndLogic;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class MultiGroupTest extends TestCase
{
    #[Group('X')]
    public function testX(): void
    {
        $this->assertTrue(true);
    }

    #[Group('Y')]
    public function testY(): void
    {
        $this->assertTrue(true);
    }

    #[Group('X')]
    #[Group('Y')]
    public function testXY(): void
    {
        $this->assertTrue(true);
    }

    #[Group('X')]
    #[Group('Y')]
    #[Group('Z')]
    public function testXYZ(): void
    {
        $this->assertTrue(true);
    }

    #[Group('Z')]
    public function testZ(): void
    {
        $this->assertTrue(true);
    }
}
