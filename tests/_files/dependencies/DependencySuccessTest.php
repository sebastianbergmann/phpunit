<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\TestCase;

class DependencySuccessTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testOne')]
    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    #[DependsExternal(self::class, 'testTwo')]
    public function testThree(): void
    {
        $this->assertTrue(true);
    }
}
