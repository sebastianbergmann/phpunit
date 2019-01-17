<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

class ProxyObjectTest extends TestCase
{
    public function testMockedMethodIsProxiedToOriginalMethod(): void
    {
        $proxy = $this->getMockBuilder(Bar::class)
                      ->enableProxyingToOriginalMethods()
                      ->getMock();

        $proxy->expects($this->once())
              ->method('doSomethingElse');

        $foo = new Foo;

        $this->assertEquals('result', $foo->doSomething($proxy));
    }

    public function testMockedMethodWithReferenceIsProxiedToOriginalMethod(): void
    {
        $proxy = $this->getMockBuilder(MethodCallbackByReference::class)
                      ->enableProxyingToOriginalMethods()
                      ->getMock();

        $a = $b = $c = 0;

        $proxy->callback($a, $b, $c);

        $this->assertEquals(1, $b);
    }
}
