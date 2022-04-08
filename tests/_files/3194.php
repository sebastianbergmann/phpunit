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

use PHPUnit\Framework\TestCase;

trait T3194
{
    public function doSomethingElse(): bool
    {
        return true;
    }
}

final class C3194
{
    use T3194;

    public function doSomething(): bool
    {
        return $this->doSomethingElse();
    }
}

/**
 * @covers \PHPUnit\TestFixture\C3194
 */
final class Test3194 extends TestCase
{
    public function testOne(): void
    {
        $o = new C;

        $this->assertTrue($o->doSomething());
    }
}
