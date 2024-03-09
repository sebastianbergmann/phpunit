<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5351;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass('PHPUnit\TestFixture\Issue5351\DoesNotExist')]
final class GreeterTest extends TestCase
{
    public function testGreets(): void
    {
        $this->assertSame('Hello world!', (new Greeter)->greet());
    }
}
