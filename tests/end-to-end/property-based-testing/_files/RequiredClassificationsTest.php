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
use PHPUnit\Framework\Attributes\RequireClassification;
use PHPUnit\Framework\TestCase;

final class RequiredClassificationsTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 10)]
    #[RequireClassification('even', 50)]
    #[RequireClassification('zero', 20)]
    public function testClassifiesTooFewExamplesAsZero(int $value): void
    {
        if ($value % 2 === 0) {
            $this->classify('even');
        }

        if ($value === 0) {
            $this->classify('zero');
        }

        $this->assertIsInt($value);
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 10)]
    #[RequireClassification('never', 1)]
    public function testIsFalsified(int $value): void
    {
        $this->assertLessThan(5, $value);
    }
}
