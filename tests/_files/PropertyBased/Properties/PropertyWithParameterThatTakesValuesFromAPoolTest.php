<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Properties;

use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class PropertyWithParameterThatTakesValuesFromAPoolTest extends TestCase
{
    #[Property]
    public function testWithParameterThatTakesValuesFromAPool(int $a, #[FromPool('values')] int $b): void
    {
        $this->assertIsInt($a + $b);
    }
}
