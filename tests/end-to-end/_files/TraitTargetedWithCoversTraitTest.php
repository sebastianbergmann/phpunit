<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\DeprecatedAnnotationsTestFixture;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\CoveredTrait;

#[CoversTrait(CoveredTrait::class)]
final class TraitTargetedWithCoversTraitTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
