<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Php::class)]
#[Small]
final class PhpTest extends TestCase
{
    private readonly DirectoryCollection $includePaths;
    private readonly IniSettingCollection $iniSettings;
    private readonly ConstantCollection $constants;
    private readonly VariableCollection $globalVariables;
    private readonly VariableCollection $envVariables;
    private readonly VariableCollection $postVariables;
    private readonly VariableCollection $getVariables;
    private readonly VariableCollection $cookieVariables;
    private readonly VariableCollection $serverVariables;
    private readonly VariableCollection $filesVariables;
    private readonly VariableCollection $requestVariables;
    private readonly Php $fixture;

    protected function setUp(): void
    {
        $this->includePaths     = DirectoryCollection::fromArray([]);
        $this->iniSettings      = IniSettingCollection::fromArray([]);
        $this->constants        = ConstantCollection::fromArray([]);
        $this->globalVariables  = VariableCollection::fromArray([]);
        $this->envVariables     = VariableCollection::fromArray([]);
        $this->postVariables    = VariableCollection::fromArray([]);
        $this->getVariables     = VariableCollection::fromArray([]);
        $this->cookieVariables  = VariableCollection::fromArray([]);
        $this->serverVariables  = VariableCollection::fromArray([]);
        $this->filesVariables   = VariableCollection::fromArray([]);
        $this->requestVariables = VariableCollection::fromArray([]);

        $this->fixture = new Php(
            $this->includePaths,
            $this->iniSettings,
            $this->constants,
            $this->globalVariables,
            $this->envVariables,
            $this->postVariables,
            $this->getVariables,
            $this->cookieVariables,
            $this->serverVariables,
            $this->filesVariables,
            $this->requestVariables,
        );
    }

    public function testHasIncludePaths(): void
    {
        $this->assertSame($this->includePaths, $this->fixture->includePaths());
    }

    public function testHasIniSettings(): void
    {
        $this->assertSame($this->iniSettings, $this->fixture->iniSettings());
    }

    public function testHasConstants(): void
    {
        $this->assertSame($this->constants, $this->fixture->constants());
    }

    public function testHasGlobalVariables(): void
    {
        $this->assertSame($this->globalVariables, $this->fixture->globalVariables());
    }

    public function testHasEnvVariables(): void
    {
        $this->assertSame($this->envVariables, $this->fixture->envVariables());
    }

    public function testHasPostVariables(): void
    {
        $this->assertSame($this->postVariables, $this->fixture->postVariables());
    }

    public function testHasGetVariables(): void
    {
        $this->assertSame($this->getVariables, $this->fixture->getVariables());
    }

    public function testHasCookieVariables(): void
    {
        $this->assertSame($this->cookieVariables, $this->fixture->cookieVariables());
    }

    public function testHasServerVariables(): void
    {
        $this->assertSame($this->serverVariables, $this->fixture->serverVariables());
    }

    public function testHasFilesVariables(): void
    {
        $this->assertSame($this->filesVariables, $this->fixture->filesVariables());
    }

    public function testHasRequestVariables(): void
    {
        $this->assertSame($this->requestVariables, $this->fixture->requestVariables());
    }
}
