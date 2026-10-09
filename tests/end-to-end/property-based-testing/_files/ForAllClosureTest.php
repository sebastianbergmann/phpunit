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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class ForAllClosureTest extends TestCase
{
    #[Property]
    public function testGeneratorIsSpecifiedWithAClosure(
        #[ForAll(static function (): ValueGenerator
        {
            return Generators::integers(1, 3);
        })]
        int $value,
    ): void {
        $this->assertContains($value, [1, 2, 3]);
    }
}
