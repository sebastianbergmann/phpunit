<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox\MethodOrderInheritance;

use PHPUnit\Framework\TestCase;

final class InheritanceTest extends ParentTestCase
{
    public function testDeclaredFirstInChild(): void
    {
        $this->assertTrue(true);
    }

    public function testDeclaredSecondInChild(): void
    {
        $this->assertTrue(true);
    }
}

abstract class ParentTestCase extends TestCase
{
    public function testDeclaredFirstInParent(): void
    {
        $this->assertTrue(true);
    }

    public function testDeclaredSecondInParent(): void
    {
        $this->assertTrue(true);
    }
}
