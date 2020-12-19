<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Application\PHP
 */
final class PHPTest extends TestCase
{
    public function testDefaults(): void
    {
        $php = new PHP();

        $this->assertSame(PHP_VERSION, $php->asString());
        $this->assertSame(PHP_SAPI, $php->sapi());
        $this->assertSame(PHP_MAJOR_VERSION, $php->major());
        $this->assertSame(PHP_MINOR_VERSION, $php->minor());
        $this->assertSame(PHP_RELEASE_VERSION, $php->patch());
        $this->assertSame(PHP_EXTRA_VERSION, $php->extra());
        $this->assertSame(PHP_VERSION_ID, $php->id());
        $this->assertEquals(new Extensions(), $php->extensions());
    }
}
