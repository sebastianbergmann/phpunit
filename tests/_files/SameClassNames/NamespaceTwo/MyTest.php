<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\NamespaceTwo;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function test2of3(): void
    {
    }

    public function test3of3(): void
    {
    }
}
