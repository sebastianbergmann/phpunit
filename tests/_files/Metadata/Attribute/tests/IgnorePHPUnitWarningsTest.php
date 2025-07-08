<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\IgnorePHPUnitWarnings;
use PHPUnit\Framework\TestCase;

final class IgnorePHPUnitWarningsTest extends TestCase
{
    #[IgnorePHPUnitWarnings]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[IgnorePHPUnitWarnings('warning.*pattern')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    #[IgnorePHPUnitWarnings('exact message')]
    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
