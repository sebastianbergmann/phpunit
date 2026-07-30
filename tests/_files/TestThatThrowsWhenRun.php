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

use PHPUnit\Framework\Test;
use RuntimeException;

final class TestThatThrowsWhenRun implements Test
{
    public function count(): int
    {
        return 1;
    }

    public function run(): void
    {
        throw new RuntimeException('the test could not be run');
    }
}
