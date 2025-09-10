<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;

#[CoversClass(Exception::class)]
#[Small]
final class ExceptionTest extends TestCase
{
    public function testExceptionSerialize(): void
    {
        $actual = (new Exception)->__serialize();

        $this->assertCount(5, $actual);
        $this->assertArrayHasKey('serializableTrace', $actual);
        $this->assertArrayHasKey('message', $actual);
        $this->assertArrayHasKey('code', $actual);
        $this->assertArrayHasKey('file', $actual);
        $this->assertArrayHasKey('line', $actual);
    }
}
