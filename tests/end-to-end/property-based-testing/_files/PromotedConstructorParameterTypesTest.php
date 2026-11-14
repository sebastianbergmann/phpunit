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

require_once __DIR__ . '/Rating.php';

final class PromotedConstructorParameterTypesTest extends TestCase
{
    #[Property]
    public function testTypeInTheVarTagOfAPromotedConstructorParameterIsUsed(Rating $rating): void
    {
        $this->assertGreaterThanOrEqual(1, $rating->stars);
        $this->assertLessThanOrEqual(5, $rating->stars);
        $this->assertIsList($rating->tags);
        $this->assertNotContains('', $rating->tags);
    }

    #[Property]
    public function testTypeInAParamTagOfTheConstructorTakesPrecedence(Rating $rating): void
    {
        $this->assertNotSame('', $rating->product);
    }

    #[Property]
    public function testIsFalsifiedByAProductThatTheVarTagDoesNotAllow(Rating $rating): void
    {
        $this->assertContains($rating->product, ['a', 'b']);
    }
}
