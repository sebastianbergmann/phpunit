<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class ProxyObjectTest extends TestCase
{
    public function testProxyingWorksForMethodThatReturnsUndeclaredScalarValue(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnString');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('result', $proxy->returnString());
    }

    public function testProxyingWorksForMethodThatReturnsDeclaredScalarValue(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnTypedString');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('result', $proxy->returnTypedString());
    }

    public function testProxyingWorksForMethodThatReturnsUndeclaredObject(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnObject');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('bar', $proxy->returnObject()->foo);
    }

    public function testProxyingWorksForMethodThatReturnsDeclaredObject(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnTypedObject');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('bar', $proxy->returnTypedObject()->foo);
    }

    public function testProxyingWorksForMethodThatReturnsUndeclaredObjectOfFinalClass(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnObjectOfFinalClass');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('value', $proxy->returnObjectOfFinalClass()->value());
    }

    public function testProxyingWorksForMethodThatReturnsDeclaredObjectOfFinalClass(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnTypedObjectOfFinalClass');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('value', $proxy->returnTypedObjectOfFinalClass()->value());
    }
}
