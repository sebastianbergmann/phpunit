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
 * @covers \PHPUnit\Event\Application\OS
 */
final class OSTest extends TestCase
{
    public function testDefaults(): void
    {
        $os = new OS();

        $this->assertSame(PHP_OS, $os->asString());
        $this->assertSame(PHP_OS_FAMILY, $os->family());
    }
}
