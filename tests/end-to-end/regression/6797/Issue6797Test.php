<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6797;

use PHPUnit\Framework\TestCase;

interface InterfaceWithConstructor
{
    public function __construct();
}

final class Issue6797Test extends TestCase
{
    public function testStubCanBeCreatedForInterfaceWithConstructor(): void
    {
        $stub = $this->createStub(InterfaceWithConstructor::class);

        $this->assertInstanceOf(InterfaceWithConstructor::class, $stub);
    }

    public function testMockCanBeCreatedForInterfaceWithConstructor(): void
    {
        $mock = $this->createMock(InterfaceWithConstructor::class);

        $this->assertInstanceOf(InterfaceWithConstructor::class, $mock);
    }
}
