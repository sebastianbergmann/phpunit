<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6138;

use PHPUnit\Framework\TestCase;

final class C
{
    private string $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }
}

interface I
{
    public function m(C $c): void;
}

final class Issue6138Test extends TestCase
{
    public function testOne(): void
    {
        $i = $this->createMock(I::class);

        $i->expects($this->once())->method('m')->with(new C('bar'));

        $i->m(new C('baz'));
    }
}
