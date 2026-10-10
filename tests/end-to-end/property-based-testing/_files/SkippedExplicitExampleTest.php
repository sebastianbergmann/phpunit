<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class SkippedExplicitExampleTest extends TestCase
{
    /**
     * @param positive-int $value
     */
    #[Property(examples: 10)]
    #[TestWith([0])]
    public function testSkipsTheExampleForZero(int $value): void
    {
        if ($value === 0) {
            $this->markTestSkipped('zero');
        }

        $this->assertGreaterThan(0, $value);
    }
}
