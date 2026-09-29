<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\RequiresMethodClassCannotBeLoaded;

use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;

final class RequiresMethodTest extends TestCase
{
    #[RequiresMethod(ClassWithMissingParent::class, 'method')]
    public function testShouldNotRun(): void
    {
        $this->fail();
    }

    public function testShouldRun(): void
    {
        $this->assertTrue(true);
    }
}
