<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelInProcessUnit;

use PHPUnit\Framework\TestCase;

final class AFirstTest extends TestCase
{
    public function testFinishesAtOnce(): void
    {
        $this->assertTrue(true);
    }
}
