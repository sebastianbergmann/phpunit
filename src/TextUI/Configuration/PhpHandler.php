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

use const PATH_SEPARATOR;
use function constant;
use function define;
use function defined;
use function getenv;
use function implode;
use function ini_get;
use function ini_set;
use function putenv;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhpHandler
{
    public function handle(DirectoryCollection $includePaths, IniSettingCollection $iniSettings, ConstantCollection $constants, VariableCollection $globalVariables, VariableCollection $envVariables, VariableCollection $postVariables, VariableCollection $getVariables, VariableCollection $cookieVariables, VariableCollection $serverVariables, VariableCollection $filesVariables, VariableCollection $requestVariables): void
    {
        $this->handleIncludePaths($includePaths);
        $this->handleIniSettings($iniSettings);
        $this->handleConstants($constants);
        $this->handleGlobalVariables($globalVariables);
        $this->handleServerVariables($serverVariables);
        $this->handleEnvVariables($envVariables);
        $this->handleVariables('_POST', $postVariables);
        $this->handleVariables('_GET', $getVariables);
        $this->handleVariables('_COOKIE', $cookieVariables);
        $this->handleVariables('_FILES', $filesVariables);
        $this->handleVariables('_REQUEST', $requestVariables);
    }

    private function handleIncludePaths(DirectoryCollection $includePaths): void
    {
        if (!$includePaths->isEmpty()) {
            $includePathsAsStrings = [];

            foreach ($includePaths as $includePath) {
                $includePathsAsStrings[] = $includePath->path();
            }

            ini_set(
                'include_path',
                implode(PATH_SEPARATOR, $includePathsAsStrings) .
                PATH_SEPARATOR .
                ini_get('include_path')
            );
        }
    }

    private function handleIniSettings(IniSettingCollection $iniSettings): void
    {
        foreach ($iniSettings as $iniSetting) {
            $value = $iniSetting->value();

            if (defined($value)) {
                $value = (string) constant($value);
            }

            ini_set($iniSetting->name(), $value);
        }
    }

    private function handleConstants(ConstantCollection $constants): void
    {
        foreach ($constants as $constant) {
            if (!defined($constant->name())) {
                define($constant->name(), $constant->value());
            }
        }
    }

    private function handleGlobalVariables(VariableCollection $variables): void
    {
        foreach ($variables as $variable) {
            $GLOBALS[$variable->name()] = $variable->value();
        }
    }

    private function handleServerVariables(VariableCollection $variables): void
    {
        foreach ($variables as $variable) {
            $_SERVER[$variable->name()] = $variable->value();
        }
    }

    private function handleVariables(string $target, VariableCollection $variables): void
    {
        foreach ($variables as $variable) {
            $GLOBALS[$target][$variable->name()] = $variable->value();
        }
    }

    private function handleEnvVariables(VariableCollection $variables): void
    {
        foreach ($variables as $variable) {
            $name  = $variable->name();
            $value = $variable->value();
            $force = $variable->force();

            if ($force || getenv($name) === false) {
                putenv("{$name}={$value}");
            }

            $value = getenv($name);

            if ($force || !isset($_ENV[$name])) {
                $_ENV[$name] = $value;
            }
        }
    }
}
