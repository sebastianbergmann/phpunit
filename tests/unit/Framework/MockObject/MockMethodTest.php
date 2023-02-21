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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Type\UnknownType;

#[CoversClass(MockMethod::class)]
#[Small]
final class MockMethodTest extends TestCase
{
    public function testGetNameReturnsMethodName(): void
    {
        $method = new MockMethod(
            'ClassName',
            'methodName',
            false,
            '',
            '',
            '',
            new UnknownType,
            '',
            false,
            false,
            null,
            false
        );
        $this->assertEquals('methodName', $method->methodName());
    }
}
