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

use function count;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class TooLargeExamplesTest extends TestCase
{
    /**
     * @return ValueGenerator<list<int>>
     */
    public static function hugeLists(): ValueGenerator
    {
        return Generators::lists(Generators::integers(), 10000);
    }

    /**
     * @param list<int> $values
     */
    #[Property]
    public function testGeneratesTooLargeExamples(#[ForAll('hugeLists')] array $values): void
    {
        $this->assertGreaterThanOrEqual(10000, count($values));
    }
}
