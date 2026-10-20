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
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OtherFalsificationsTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsFalsifiedInTwoWays(int $value): void
    {
        if ($value >= 100 && $value < 500) {
            throw new RuntimeException('between 100 and 500');
        }

        $this->assertLessThan(500, $value);
    }

    #[Property]
    public function testDrawsAValueThatFalsifiesItInTwoWays(): void
    {
        $value = $this->draw(Generators::integers(0, 1000), 'value');

        if ($value >= 50 && $value < 200) {
            throw new RuntimeException('between 50 and 200');
        }

        $this->assertLessThan(200, $value);
    }
}
