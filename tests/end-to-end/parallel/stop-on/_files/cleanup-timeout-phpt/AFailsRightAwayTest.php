<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelStopOn;

use PHPUnit\Framework\TestCase;

final class AFailsRightAwayTest extends TestCase
{
    public function testFails(): void
    {
        $this->fail('failure');
    }
}
