<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtensionCapabilities::class)]
#[Small]
#[Group('test-runner')]
final class ExtensionCapabilitiesTest extends TestCase
{
    public function testHasNoCapabilitiesWhenNoExtensionIsBootstrapped(): void
    {
        $capabilities = ExtensionCapabilities::none();

        $this->assertFalse($capabilities->requiresCodeCoverageCollection());
        $this->assertFalse($capabilities->replacesOutput());
        $this->assertFalse($capabilities->replacesProgressOutput());
        $this->assertFalse($capabilities->replacesResultOutput());
    }

    public function testCanRequireCodeCoverageCollection(): void
    {
        $capabilities = ExtensionCapabilities::from(true, false, false, false);

        $this->assertTrue($capabilities->requiresCodeCoverageCollection());
        $this->assertFalse($capabilities->replacesOutput());
        $this->assertFalse($capabilities->replacesProgressOutput());
        $this->assertFalse($capabilities->replacesResultOutput());
    }

    public function testCanReplaceOutput(): void
    {
        $capabilities = ExtensionCapabilities::from(false, true, false, false);

        $this->assertFalse($capabilities->requiresCodeCoverageCollection());
        $this->assertTrue($capabilities->replacesOutput());
        $this->assertFalse($capabilities->replacesProgressOutput());
        $this->assertFalse($capabilities->replacesResultOutput());
    }

    public function testCanReplaceProgressOutput(): void
    {
        $capabilities = ExtensionCapabilities::from(false, false, true, false);

        $this->assertFalse($capabilities->requiresCodeCoverageCollection());
        $this->assertFalse($capabilities->replacesOutput());
        $this->assertTrue($capabilities->replacesProgressOutput());
        $this->assertFalse($capabilities->replacesResultOutput());
    }

    public function testCanReplaceResultOutput(): void
    {
        $capabilities = ExtensionCapabilities::from(false, false, false, true);

        $this->assertFalse($capabilities->requiresCodeCoverageCollection());
        $this->assertFalse($capabilities->replacesOutput());
        $this->assertFalse($capabilities->replacesProgressOutput());
        $this->assertTrue($capabilities->replacesResultOutput());
    }
}
