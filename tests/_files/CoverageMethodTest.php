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

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\UsesMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(CoveredClass::class, 'publicMethod')]
#[UsesMethod(CoveredClass::class, 'publicMethod')]
final class CoverageMethodTest extends TestCase
{
    public function testSomething(): void
    {
        $o = new CoveredClass;

        $o->publicMethod();
    }
}
