<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\Constant;
use PHPUnit\TextUI\Configuration\ConstantCollection;
use PHPUnit\TextUI\Configuration\Directory;
use PHPUnit\TextUI\Configuration\DirectoryCollection;
use PHPUnit\TextUI\Configuration\IniSetting;
use PHPUnit\TextUI\Configuration\IniSettingCollection;
use PHPUnit\TextUI\Configuration\Php;
use PHPUnit\TextUI\Configuration\Variable;
use PHPUnit\TextUI\Configuration\VariableCollection;

#[CoversClass(ExecutionSettings::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class ExecutionSettingsTest extends TestCase
{
    /**
     * @return array<non-empty-string, array{0: int}>
     */
    public static function provideKindsOfVariables(): array
    {
        return [
            'global'  => [3],
            'env'     => [4],
            'post'    => [5],
            'get'     => [6],
            'cookie'  => [7],
            'server'  => [8],
            'files'   => [9],
            'request' => [10],
        ];
    }

    public function testAreTheSameWhenNothingChanged(): void
    {
        $this->assertSame($this->settings()->hash(), $this->settings()->hash());
    }

    public function testAreNotTheSameWhenAnIncludePathIsAdded(): void
    {
        $this->assertNotSame(
            $this->settings()->hash(),
            $this->settings($this->php([new Directory('/lib')]))->hash(),
        );
    }

    public function testAreNotTheSameWhenAPhpSettingChanged(): void
    {
        $this->assertNotSame(
            $this->settings($this->php([], [new IniSetting('precision', '14')]))->hash(),
            $this->settings($this->php([], [new IniSetting('precision', '17')]))->hash(),
        );
    }

    public function testAreNotTheSameWhenPhpSettingsAreConfiguredInAnotherOrder(): void
    {
        $first  = new IniSetting('precision', '14');
        $second = new IniSetting('precision', '17');

        $this->assertNotSame(
            $this->settings($this->php([], [$first, $second]))->hash(),
            $this->settings($this->php([], [$second, $first]))->hash(),
        );
    }

    public function testAreNotTheSameWhenAConstantChanged(): void
    {
        $this->assertNotSame(
            $this->settings($this->php([], [], [new Constant('DEBUG', true)]))->hash(),
            $this->settings($this->php([], [], [new Constant('DEBUG', false)]))->hash(),
        );
    }

    #[DataProvider('provideKindsOfVariables')]
    public function testAreNotTheSameWhenAVariableChanged(int $kind): void
    {
        $this->assertNotSame(
            $this->settings($this->phpWithVariable($kind, new Variable('APP_ENV', 'test', false)))->hash(),
            $this->settings($this->phpWithVariable($kind, new Variable('APP_ENV', 'prod', false)))->hash(),
        );
    }

    public function testAreNotTheSameWhenAVariableIsForced(): void
    {
        $this->assertNotSame(
            $this->settings($this->phpWithVariable(4, new Variable('APP_ENV', 'test', false)))->hash(),
            $this->settings($this->phpWithVariable(4, new Variable('APP_ENV', 'test', true)))->hash(),
        );
    }

    public function testAreNotTheSameWhenTheSameVariableIsOfAnotherKind(): void
    {
        $variable = new Variable('APP_ENV', 'test', false);

        $this->assertNotSame(
            $this->settings($this->phpWithVariable(4, $variable))->hash(),
            $this->settings($this->phpWithVariable(8, $variable))->hash(),
        );
    }

    public function testAreNotTheSameWhenATestSuiteIsBootstrappedByAnotherScript(): void
    {
        $this->assertNotSame(
            ExecutionSettings::from($this->php(), ['unit' => '/a.php', 'integration' => '/b.php'], [], false, false, false, false)->hash(),
            ExecutionSettings::from($this->php(), ['unit' => '/b.php', 'integration' => '/a.php'], [], false, false, false, false)->hash(),
        );
    }

    public function testAreNotTheSameWhenAnExtensionIsLoaded(): void
    {
        $this->assertNotSame(
            $this->settings()->hash(),
            ExecutionSettings::from($this->php(), [], [['className' => 'Extension', 'parameters' => []]], false, false, false, false)->hash(),
        );
    }

    public function testAreNotTheSameWhenAnExtensionIsConfiguredDifferently(): void
    {
        $this->assertNotSame(
            ExecutionSettings::from($this->php(), [], [['className' => 'Extension', 'parameters' => ['mode' => 'a']]], false, false, false, false)->hash(),
            ExecutionSettings::from($this->php(), [], [['className' => 'Extension', 'parameters' => ['mode' => 'b']]], false, false, false, false)->hash(),
        );
    }

    public function testAreTheSameWhenAnExtensionIsConfiguredButNotLoaded(): void
    {
        $this->assertSame(
            $this->settings()->hash(),
            ExecutionSettings::from($this->php(), [], [['className' => 'Extension', 'parameters' => []]], true, false, false, false)->hash(),
        );
    }

    public function testAreNotTheSameWhenTestsAreRunInIsolation(): void
    {
        $this->assertNotSame(
            $this->settings()->hash(),
            ExecutionSettings::from($this->php(), [], [], false, true, false, false)->hash(),
        );
    }

    public function testAreNotTheSameWhenGlobalVariablesAreBackedUp(): void
    {
        $this->assertNotSame(
            $this->settings()->hash(),
            ExecutionSettings::from($this->php(), [], [], false, false, true, false)->hash(),
        );
    }

    public function testAreNotTheSameWhenStaticPropertiesAreBackedUp(): void
    {
        $this->assertNotSame(
            $this->settings()->hash(),
            ExecutionSettings::from($this->php(), [], [], false, false, false, true)->hash(),
        );
    }

    private function settings(?Php $php = null): ExecutionSettings
    {
        if ($php === null) {
            $php = $this->php();
        }

        return ExecutionSettings::from($php, [], [], false, false, false, false);
    }

    /**
     * @param list<Directory>  $includePaths
     * @param list<IniSetting> $iniSettings
     * @param list<Constant>   $constants
     */
    private function php(array $includePaths = [], array $iniSettings = [], array $constants = []): Php
    {
        return new Php(
            DirectoryCollection::fromArray($includePaths),
            IniSettingCollection::fromArray($iniSettings),
            ConstantCollection::fromArray($constants),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
            VariableCollection::fromArray([]),
        );
    }

    /**
     * @param int $kind the position of the variables among the arguments of Php's constructor
     */
    private function phpWithVariable(int $kind, Variable $variable): Php
    {
        $arguments = [
            DirectoryCollection::fromArray([]),
            IniSettingCollection::fromArray([]),
            ConstantCollection::fromArray([]),
        ];

        for ($position = 3; $position <= 10; $position++) {
            $variables = [];

            if ($position === $kind) {
                $variables[] = $variable;
            }

            $arguments[] = VariableCollection::fromArray($variables);
        }

        return new Php(...$arguments);
    }
}
