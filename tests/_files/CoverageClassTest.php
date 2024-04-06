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

#[CoversClass(CoveredClass::class)]
#[UsesClass(CoveredClass::class)]
final class CoverageClassTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;

        $o->publicMethod();
    }
}
