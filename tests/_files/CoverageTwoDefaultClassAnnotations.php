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

/**
 * @coversDefaultClass \NamespaceOne
 * @coversDefaultClass \AnotherDefault\Name\Space\Does\Not\Work
 */
class CoverageTwoDefaultClassAnnotations
{
    /**
     * @covers \PHPUnit\TestFixture\CoveredClass::<public>
     */
    public function testSomething(): void
    {
        $o = new CoveredClass;

        $o->publicMethod();
    }
}
