<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverageDriver;

use PHPUnit\Framework\TestCase;

final class FooTest extends TestCase
{
    public function testValue(): void
    {
        $this->assertSame(1, (new Foo)->value());
    }
}
