<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Phar;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

#[CoversClass(Greeter::class)]
final class GreeterTest extends TestCase
{
    public function testGreets(): void
    {
        $this->assertSame('Hello world!', (new Greeter)->greet());
    }

    #[RunInSeparateProcess]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/4412')]
    public function testGreetsInIsolation(): void
    {
        $this->assertSame('Hello world!', (new Greeter)->greet());
    }
}
