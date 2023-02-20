<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5218;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Issue5218::class)]
final class Issue5218Test extends TestCase
{
    public function testReturn(): void
    {
        $this->assertSame('foo', (new Issue5218)->returnMe('foo'));
    }
}
