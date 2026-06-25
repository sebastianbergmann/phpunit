<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6778;

use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequiresMethodTest extends TestCase
{
    #[Test]
    #[RequiresMethod(DeprecatedClass::class, 'foo')]
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
