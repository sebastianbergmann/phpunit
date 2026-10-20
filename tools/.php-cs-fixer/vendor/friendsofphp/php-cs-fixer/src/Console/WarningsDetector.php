<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console;

use PhpCsFixer\ComposerJsonReader;
use PhpCsFixer\ToolInfo;
use PhpCsFixer\ToolInfoInterface;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class WarningsDetector
{
    private ToolInfoInterface $toolInfo;

    /**
     * @var list<string>
     */
    private array $warnings = [];

    public function __construct(ToolInfoInterface $toolInfo)
    {
        $this->toolInfo = $toolInfo;
    }

    public function detectOldMajor(): void
    {
        // @TODO 3.99 to be activated with new MAJOR release 4.0
        // $currentMajorVersion = \intval(explode('.', Application::VERSION)[0], 10);
        // $nextMajorVersion = $currentMajorVersion + 1;
        // $this->warnings[] = "You are running PHP CS Fixer v{$currentMajorVersion}, which is not maintained anymore. Please update to v{$nextMajorVersion}.";
        // $this->warnings[] = "You may find an UPGRADE guide at https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v{$nextMajorVersion}.0.0/UPGRADE-v{$nextMajorVersion}.md .";
    }

    public function detectOldVendor(): void
    {
        if ($this->toolInfo->isInstalledByComposer()) {
            $details = $this->toolInfo->getComposerInstallationDetails();
            if (ToolInfo::COMPOSER_LEGACY_PACKAGE_NAME === $details['name']) {
                $this->warnings[] = \sprintf(
                    'You are running PHP CS Fixer installed with old vendor `%s`. Please update to `%s`.',
                    ToolInfo::COMPOSER_LEGACY_PACKAGE_NAME,
                    ToolInfo::COMPOSER_PACKAGE_NAME,
                );
            }
        }
    }

    public function detectNonMonolithic(): void
    {
        if (filter_var(getenv('PHP_CS_FIXER_NON_MONOLITHIC'), \FILTER_VALIDATE_BOOL)) {
            $this->warnings[] = 'Processing non-monolithic files enabled, because `PHP_CS_FIXER_NON_MONOLITHIC` is set. Execution result may be unpredictable - non-monolithic files are not officially supported.';
        }
    }

    public function detectHigherPhpVersion(): void
    {
        try {
            $composerJsonReader = ComposerJsonReader::createSingleton();
            $minPhpVersion = $composerJsonReader->getPhp();

            if (null === $minPhpVersion) {
                $this->warnings[] = 'No PHP version requirement found in composer.json. It is recommended to specify a minimum PHP version supported by your project.';

                return;
            }

            $currentPhpVersion = \PHP_VERSION;
            $currentPhpMajorMinor = \sprintf('%d.%d', \PHP_MAJOR_VERSION, \PHP_MINOR_VERSION);

            // Compare major.minor versions
            if (version_compare($currentPhpMajorMinor, $minPhpVersion, '>')) {
                $this->warnings[] = \sprintf(
                    'You are running PHP CS Fixer on PHP %1$s, but the minimum PHP version supported by your project in composer.json is PHP %2$s. Executing PHP CS Fixer on newer PHP versions may introduce syntax or features not yet available in PHP %2$s, which could cause issues under that version. It is recommended to run PHP CS Fixer on PHP %2$s, to fit your project specifics.',
                    $currentPhpVersion,
                    $minPhpVersion,
                );
            }
        } catch (\Throwable $e) {
            $this->warnings[] = \sprintf(
                'Unable to determine minimum PHP version supported by your project from composer.json: %s',
                $e->getMessage(),
            );
        }
    }

    /**
     * @return list<string>
     */
    public function getWarnings(): array
    {
        if (0 === \count($this->warnings)) {
            return [];
        }

        return array_values(array_unique(array_merge(
            $this->warnings,
            ['If you need help while solving warnings, ask at https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/discussions/, we will help you!'],
        )));
    }
}
