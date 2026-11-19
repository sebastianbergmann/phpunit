<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelProperty;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class BIsStillRunTest extends TestCase
{
    #[Property(10)]
    public function testHolds(int $value): void
    {
        $this->assertIsInt($value);
    }
}
