<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function assert;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\TestProxyFixture;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/test-proxy')]
#[Medium]
#[TestDox('createTestProxy()')]
final class CreateTestProxyTest extends TestCase
{
    public function testCreatesTestProxyForExtendableClass(): void
    {
        $proxy = $this->createTestProxy(TestProxyFixture::class);

        $proxy->expects($this->once())
            ->method('returnString');

        assert($proxy instanceof MockObject);
        assert($proxy instanceof TestProxyFixture);

        $this->assertSame('result', $proxy->returnString());
    }
}
