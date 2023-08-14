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

use function var_dump;
use PHPUnit\Framework\TestCase;

final class UnexpectedOutputTest extends TestCase
{
    public function testSomething(): void
    {
        var_dump(['foo' => 'bar']);

        $this->assertSame('something', 'something');
    }
}
