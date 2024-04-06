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
final class NamespaceCoverageCoversClassPublicTest extends TestCase
{
    /**
     * @covers ::publicMethod
     *
     * @uses ::publicMethod
     */
    public function testSomething(): void
    {
        $o = new CoveredClass;
        $o->publicMethod();
    }
}
