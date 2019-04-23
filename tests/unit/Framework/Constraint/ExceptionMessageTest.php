<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class ExceptionMessageTest extends TestCase
{
    public function testLiteralMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A literal exception message');

        throw new \Exception('A literal exception message');
    }

    public function testPartialMessageBegin(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A partial');

        throw new \Exception('A partial exception message');
    }

    public function testPartialMessageMiddle(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('partial exception');

        throw new \Exception('A partial exception message');
    }

    public function testPartialMessageEnd(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exception message');

        throw new \Exception('A partial exception message');
    }
}
