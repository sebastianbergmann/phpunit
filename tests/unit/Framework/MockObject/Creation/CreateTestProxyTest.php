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
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\TestProxyFixture;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/test-proxy')]
#[Medium]
#[TestDox('createTestProxy()')]
#[IgnorePhpunitDeprecations]
final class CreateTestProxyTest extends TestCase
{
    public function testCreatesTestProxyForExtendableClass(): void
    {
        $double = $this->createTestProxy(TestProxyFixture::class);

        $double->expects($this->once())
            ->method('returnString');

        assert($double instanceof MockObject);
        assert($double instanceof TestProxyFixture);

        $this->assertSame('result', $double->returnString());
    }
}
