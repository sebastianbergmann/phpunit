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

use Countable;
use PHPUnit\Framework\Test;

final class NonReorderableTest implements Countable, Test
{
    public function count(): int
    {
        return 1;
    }

    public function run(): void
    {
    }
}
