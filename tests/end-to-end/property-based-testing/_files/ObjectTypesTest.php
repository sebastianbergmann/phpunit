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

require_once __DIR__ . '/Node.php';

final class ObjectTypesTest extends TestCase
{
    #[Property]
    public function testConcreteClass(Money $money): void
    {
        $this->assertGreaterThanOrEqual(0, $money->amount);
        $this->assertContains($money->currency, Currency::cases());
    }

    #[Property]
    public function testRecursiveConstruction(Node $node): void
    {
        $this->assertLessThanOrEqual(6, $node->length());
    }
}
