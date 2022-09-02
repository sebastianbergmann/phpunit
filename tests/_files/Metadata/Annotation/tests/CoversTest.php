<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Annotation;

use PHPUnit\Framework\TestCase;

/**
 * @covers ::\PHPUnit\TestFixture\Metadata\Annotation\f()
 * @covers \PHPUnit\TestFixture\Metadata\Annotation\Example
 *
 * @coversNothing
 *
 * @coversDefaultClass \PHPUnit\TestFixture\Metadata\Annotation\Example
 */
final class CoversTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testOne(): void
    {
    }

    /**
     * @covers Foo::bar
     */
    public function testTwo(): void
    {
    }
}
