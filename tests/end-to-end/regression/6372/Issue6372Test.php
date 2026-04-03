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

use Exception;
use PHPUnit\Framework\TestCase;

class Foo6372
{
    public function doFoo(string $param): void
    {
    }
}

class Bar6372
{
    public function __construct(private readonly Foo6372 $foo)
    {
    }

    public function doBar(string $param): void
    {
        try {
            $this->foo->doFoo($param);
        } catch (Exception $e) {
        }
    }
}

final class Issue6372Test extends TestCase
{
    public function testAssertionFailureInMockCallbackIsNotSwallowedByCodeUnderTest(): void
    {
        $mock = $this->createMock(Foo6372::class);

        $mock->expects($this->once())
            ->method('doFoo')
            ->willReturnCallback(function (string $param): void
            {
                $this->assertSame('expected', $param);
            });

        $bar = new Bar6372($mock);
        $bar->doBar('actual');
    }
}
