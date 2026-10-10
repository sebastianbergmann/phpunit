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

use function str_contains;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class RegularExpressionTest extends TestCase
{
    /**
     * @return ValueGenerator<string>
     */
    public static function slugs(): ValueGenerator
    {
        return Generators::fromRegularExpression('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/');
    }

    #[Property]
    public function testSlugDoesNotContainAHyphen(#[ForAll('slugs')] string $slug): void
    {
        $this->assertFalse(str_contains($slug, '-'));
    }
}
