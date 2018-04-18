<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue2366
{
    public function foo(): bool
    {
        return false;
    }
}

class Issue2366Test extends TestCase
{
    /**
     * @dataProvider provider
     *
     * @param mixed $o
     */
    public function testOne($o): void
    {
        $this->assertEquals(1, $o->foo());
    }

    public function provider()
    {
        $o = $this->createMock(Issue2366::class);

        $o->method('foo')->willReturn(1);

        return [
            [$o],
            [$o]
        ];
    }
}
