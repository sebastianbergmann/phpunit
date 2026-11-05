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

final class TargetedScoreTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property(examples: 20)]
    #[TestWith([5000])]
    public function testTargetsTheValue(int $value): void
    {
        $this->target('value', $value);

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
