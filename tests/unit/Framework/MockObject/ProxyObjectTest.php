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

final class ProxyObjectTest extends TestCase
{
    public function testProxyingMethodWithUndeclaredScalarReturnTypeWorks(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnString');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('result', $proxy->returnString());
    }

    public function testProxyingMethodWithDeclaredScalarReturnTypeWorks(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
              ->method('returnTypedString');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertSame('result', $proxy->returnTypedString());
    }

    public function testProxyingMethodWithUndeclaredObjectReturnTypeWorks(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
            ->method('returnObject');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertInstanceOf(TestProxyFixture::class, $proxy->returnObject());
    }

    public function testProxyingMethodWithDeclaredObjectReturnTypeWorks(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
            ->method('returnTypedObject');

        \assert($proxy instanceof MockObject);
        \assert($proxy instanceof TestProxyFixture);

        $this->assertInstanceOf(TestProxyFixture::class, $proxy->returnTypedObject());
    }
}
