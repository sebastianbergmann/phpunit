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

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

final class InvalidDependencyTest extends TestCase
{
    #[Depends('doesNotExist')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[DependsOnClass('DoesNotExist')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
