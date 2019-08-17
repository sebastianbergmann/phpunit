<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

final class MockTraitTest extends TestCase
{
    public function testGenerateClassFromSource(): void
    {
        $mockName = 'PHPUnit\TestFixture\MockObject\MockTraitGenerated';

        $file = __DIR__ . '/../../../_files/mock-object/MockTraitGenerated.tpl';

        $mockTrait = new MockTrait(\file_get_contents($file), $mockName);
        $mockTrait->generate();

        $this->assertTrue(\trait_exists($mockName));
    }

    public function testGenerateReturnsNameOfGeneratedClass(): void
    {
        $mockName = 'PHPUnit\TestFixture\MockObject\MockTraitGenerated';

        $mockTrait = new MockTrait('', $mockName);

        $this->assertEquals($mockName, $mockTrait->generate());
    }
}
