<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Phar\PropertyRunner;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testFailsForValuesGreaterThan100(int $value): void
    {
        $this->assertLessThanOrEqual(100, $value);
    }

    /**
     * @param list<int<0, 9>> $values
     */
    #[Property]
    public function testDrawsAValue(array $values): void
    {
        $this->assertIsInt($this->draw(Generators::integers(0, 9), 'digit'));
    }
}
