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
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Currency.php';

require_once __DIR__ . '/Money.php';

final class DiscardedDocCommentsTest extends TestCase
{
    /**
     * @param int<0, 10> $value
     */
    #[Property]
    public function testTypeInAParamTagIsUsed(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(10, $value);
    }

    #[Property]
    public function testTypeInAParamTagOfAConstructorIsUsed(Money $money): void
    {
        $this->assertGreaterThanOrEqual(0, $money->amount);
    }

    /**
     * Does not declare the type of its parameter.
     */
    #[Property]
    public function testNativeTypeIsUsedWithoutATypeInAParamTag(int $value): void
    {
        $this->assertIsInt($value);
    }

    #[Property]
    public function testNativeTypeIsUsedWithoutADocComment(bool $value): void
    {
        $this->assertIsBool($value);
    }
}
