<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\requires_class;

use PHPUnit\Framework\Attributes\RequiresClass;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    #[RequiresClass(TestCase::class)]
    public function testExistingClass(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresClass(self::class)]
    public function testTheTestClassItself(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresClass('PHPUnit\TestFixture\requires_class\ClassThatDoesNotExist')]
    public function testShouldNotRunClassDoesNotExist(): void
    {
        $this->fail();
    }
}
