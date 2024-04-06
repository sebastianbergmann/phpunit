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

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PHPUnit\TestFixture\CoveredClass
 *
 * @usesDefaultClass \PHPUnit\TestFixture\CoveredClass
 */
final class NamespaceCoverageCoversClassTest extends TestCase
{
    /**
     * @covers ::privateMethod
     * @covers ::protectedMethod
     * @covers ::publicMethod
     * @covers \PHPUnit\TestFixture\CoveredParentClass::privateMethod
     * @covers \PHPUnit\TestFixture\CoveredParentClass::protectedMethod
     * @covers \PHPUnit\TestFixture\CoveredParentClass::publicMethod
     *
     * @uses ::privateMethod
     * @uses ::protectedMethod
     * @uses ::publicMethod
     * @uses \PHPUnit\TestFixture\CoveredParentClass::privateMethod
     * @uses \PHPUnit\TestFixture\CoveredParentClass::protectedMethod
     * @uses \PHPUnit\TestFixture\CoveredParentClass::publicMethod
     */
    public function testSomething(): void
    {
        $o = new CoveredClass;

        $o->publicMethod();
    }
}
