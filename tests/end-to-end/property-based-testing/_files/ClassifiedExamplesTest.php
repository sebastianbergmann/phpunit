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

final class ClassifiedExamplesTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property(examples: 10)]
    #[TestWith([100])]
    public function testClassifiesItsExamples(int $value): void
    {
        if ($value % 2 === 0) {
            $this->classify('even');
        } else {
            $this->classify('odd');
        }

        if ($value === 0) {
            $this->classify('zero');
        }

        if ($value === 100) {
            $this->classify('explicit');
        }

        $this->assume($value !== 9);

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
