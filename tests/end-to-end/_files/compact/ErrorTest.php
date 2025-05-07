<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestCompactResultPrinter;

use Exception;
use PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
    public function testOne(): void
    {
        throw new Exception('message one');
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    public function testThree(): void
    {
        throw new Exception('message two');
    }
}
