<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox;

use const PHP_EOL;
use PHPUnit\Framework\TestCase;

final class DiffTest extends TestCase
{
    public function testSomethingThatDoesNotWork(): void
    {
        $this->assertEquals(
            'foo' . PHP_EOL . 'bar' . PHP_EOL . 'baz' . PHP_EOL,
            'foo' . PHP_EOL . 'baz' . PHP_EOL . 'bar' . PHP_EOL,
        );
    }
}
