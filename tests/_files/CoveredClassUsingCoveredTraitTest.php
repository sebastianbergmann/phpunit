<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CoveredClassUsingCoveredTrait::class)]
#[UsesClass(CoveredClassUsingCoveredTrait::class)]
final class CoveredClassUsingCoveredTraitTest extends TestCase
{
    public function testSomething(): void
    {
    }
}
