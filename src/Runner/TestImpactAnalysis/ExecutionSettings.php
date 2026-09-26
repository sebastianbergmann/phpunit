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

use function hash;
use function implode;
use function var_export;
use PHPUnit\TextUI\Configuration\Php;
use PHPUnit\TextUI\Configuration\VariableCollection;

/**
 * The settings of the configuration that can change what code a test
 * executes.
 *
 * Most of what is configured decides how the result of a test run is reported,
 * or when a test run stops, and not what a test does: whether there are
 * colours, where a log is written to, which code coverage report is generated,
 * whether a test run stops on the first defect, or in which order the tests
 * are run. What a test depended on when it was recorded does not change when
 * any of that does, and the tools that run PHPUnit with a configuration of
 * their own, a mutation testing tool for instance, change exactly that.
 *
 * What is named here is what can change which code a test executes: the PHP
 * settings, constants and variables that are configured, which test suite is
 * bootstrapped by which script, the extensions that are loaded, and whether a
 * test is run in a process of its own and with the global state it found. Whatever is not named here is not taken into account,
 * which is why only settings that are known to matter are named.
 *
 * The settings are described in the order they are configured in: the same
 * PHP setting configured twice has the value it was configured with last.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ExecutionSettings
{
    /**
     * @var non-empty-string
     */
    private string $hash;

    /**
     * @param array<non-empty-string, non-empty-string>                                   $bootstrapForTestSuite
     * @param list<array{className: non-empty-string, parameters: array<string, string>}> $extensionBootstrappers
     */
    public static function from(Php $php, array $bootstrapForTestSuite, array $extensionBootstrappers, bool $noExtensions, bool $processIsolation, bool $backupGlobals, bool $backupStaticProperties): self
    {
        $description = [];

        foreach ($bootstrapForTestSuite as $testSuite => $bootstrap) {
            $description[] = 'bootstrap-for-test-suite ' . $testSuite . ' ' . $bootstrap;
        }

        foreach ($php->includePaths() as $includePath) {
            $description[] = 'include-path ' . $includePath->path();
        }

        foreach ($php->iniSettings() as $iniSetting) {
            $description[] = 'ini ' . $iniSetting->name() . ' ' . $iniSetting->value();
        }

        foreach ($php->constants() as $constant) {
            $description[] = 'const ' . $constant->name() . ' ' . var_export($constant->value(), true);
        }

        self::describeVariables($description, 'var', $php->globalVariables());
        self::describeVariables($description, 'env', $php->envVariables());
        self::describeVariables($description, 'post', $php->postVariables());
        self::describeVariables($description, 'get', $php->getVariables());
        self::describeVariables($description, 'cookie', $php->cookieVariables());
        self::describeVariables($description, 'server', $php->serverVariables());
        self::describeVariables($description, 'files', $php->filesVariables());
        self::describeVariables($description, 'request', $php->requestVariables());

        if (!$noExtensions) {
            foreach ($extensionBootstrappers as $extensionBootstrapper) {
                $description[] = 'extension ' . $extensionBootstrapper['className'] . ' ' . var_export($extensionBootstrapper['parameters'], true);
            }
        }

        $description[] = 'process-isolation ' . var_export($processIsolation, true);
        $description[] = 'backup-globals ' . var_export($backupGlobals, true);
        $description[] = 'backup-static-properties ' . var_export($backupStaticProperties, true);

        return new self(hash('xxh128', implode("\n", $description)));
    }

    /**
     * @param non-empty-string $hash
     */
    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return non-empty-string
     */
    public function hash(): string
    {
        return $this->hash;
    }

    /**
     * @param list<string> $description
     */
    private static function describeVariables(array &$description, string $kind, VariableCollection $variables): void
    {
        foreach ($variables as $variable) {
            $description[] = $kind . ' ' . $variable->name() . ' ' . var_export($variable->value(), true) . ' ' . var_export($variable->force(), true);
        }
    }
}
