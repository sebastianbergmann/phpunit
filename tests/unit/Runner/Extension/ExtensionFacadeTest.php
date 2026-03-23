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

#[CoversClass(ExtensionFacade::class)]
#[Small]
#[Group('test-runner')]
final class ExtensionFacadeTest extends TestCase
{
    public function testDoesNotReplaceOutputByDefault(): void
    {
        $facade = new ExtensionFacade;

        $this->assertFalse($facade->replacesOutput());
    }

    public function testOutputCanBeReplaced(): void
    {
        $facade = new ExtensionFacade;

        $facade->replaceOutput();

        $this->assertTrue($facade->replacesOutput());
    }

    public function testDoesNotReplaceProgressOutputByDefault(): void
    {
        $facade = new ExtensionFacade;

        $this->assertFalse($facade->replacesProgressOutput());
    }

    public function testProgressOutputCanBeReplaced(): void
    {
        $facade = new ExtensionFacade;

        $facade->replaceProgressOutput();

        $this->assertTrue($facade->replacesProgressOutput());
    }

    public function testReplacingOutputAlsoReplacesProgressOutput(): void
    {
        $facade = new ExtensionFacade;

        $facade->replaceOutput();

        $this->assertTrue($facade->replacesProgressOutput());
    }

    public function testDoesNotReplaceResultOutputByDefault(): void
    {
        $facade = new ExtensionFacade;

        $this->assertFalse($facade->replacesResultOutput());
    }

    public function testResultOutputCanBeReplaced(): void
    {
        $facade = new ExtensionFacade;

        $facade->replaceResultOutput();

        $this->assertTrue($facade->replacesResultOutput());
    }

    public function testReplacingOutputAlsoReplacesResultOutput(): void
    {
        $facade = new ExtensionFacade;

        $facade->replaceOutput();

        $this->assertTrue($facade->replacesResultOutput());
    }

    public function testDoesNotRequireCodeCoverageCollectionByDefault(): void
    {
        $facade = new ExtensionFacade;

        $this->assertFalse($facade->requiresCodeCoverageCollection());
    }

    public function testCodeCoverageCollectionCanBeRequired(): void
    {
        $facade = new ExtensionFacade;

        $facade->requireCodeCoverageCollection();

        $this->assertTrue($facade->requiresCodeCoverageCollection());
    }
}
