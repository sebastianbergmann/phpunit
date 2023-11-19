<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5567;

use PHPUnit\Framework\TestCase;

final class Issue5567Test extends TestCase
{
    public function testAnythingThatFailsWithRecursiveArray(): void
    {
        $array         = [];
        $array['self'] = &$array;

        $this->assertFalse($array);
    }
}
