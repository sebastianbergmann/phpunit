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
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/CustomValueGenerator.php';

final class CustomValueGeneratorTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function custom(): ValueGenerator
    {
        return new CustomValueGenerator;
    }

    #[Property]
    public function testUsesACustomImplementationOfValueGenerator(#[ForAll('custom')] int $value): void
    {
    }
}
