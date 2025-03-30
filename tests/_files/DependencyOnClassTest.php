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

use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

final class DependencyOnClassTest extends TestCase
{
    #[DependsOnClass(DependencySuccessTest::class)]
    public function testThatDependsOnASuccessfulClass(): void
    {
        $this->assertTrue(true);
    }

    #[DependsOnClass(DependencyFailureTest::class)]
    public function testThatDependsOnAFailingClass(): void
    {
        $this->assertTrue(true);
    }
}
