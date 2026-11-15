<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use const PHP_VERSION;
use function array_shift;
use function getcwd;
use function hash_file;
use DateTimeImmutable;
use PHPUnit\Runner\Extension\PharLoader;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\Configuration\BootstrapLoader;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class InvocationBuilder
{
    private BootstrapLoader $bootstrapLoader;
    private PharLoader $pharLoader;

    public function __construct(BootstrapLoader $bootstrapLoader, PharLoader $pharLoader)
    {
        $this->bootstrapLoader = $bootstrapLoader;
        $this->pharLoader      = $pharLoader;
    }

    /**
     * Nothing that the configuration names is loaded or executed: the
     * bootstrap scripts and the extensions are named by the code that goes on
     * to load them.
     *
     * @param list<string>      $argv
     * @param ?non-empty-string $phar the PHAR that PHPUnit is run from
     */
    public function build(Configuration $configuration, array $argv, ?string $phar): Invocation
    {
        $pharChecksum              = null;
        $configurationFile         = null;
        $configurationFileChecksum = null;
        $extensionClasses          = [];
        $extensionPhars            = [];

        if ($phar !== null) {
            $pharChecksum = $this->checksum($phar);
        }

        if ($configuration->hasConfigurationFile()) {
            $configurationFile         = $configuration->configurationFile();
            $configurationFileChecksum = $this->checksum($configurationFile);
        }

        if (!$configuration->noExtensions()) {
            foreach ($configuration->extensionBootstrappers() as $bootstrapper) {
                $extensionClasses[] = $bootstrapper['className'];
            }

            if ($configuration->hasPharExtensionDirectory()) {
                $extensionPhars = $this->pharLoader->pharFilesInDirectory($configuration->pharExtensionDirectory());
            }
        }

        $workingDirectory = getcwd();

        // @codeCoverageIgnoreStart
        if ($workingDirectory === false) {
            $workingDirectory = '';
        }
        // @codeCoverageIgnoreEnd

        return new Invocation(
            Version::id(),
            $phar,
            $pharChecksum,
            PHP_VERSION,
            $this->arguments($argv),
            $workingDirectory,
            $configurationFile,
            $configurationFileChecksum,
            $this->bootstrapLoader->scripts($configuration),
            $extensionClasses,
            $extensionPhars,
            new DateTimeImmutable,
        );
    }

    /**
     * The name of the script is not an argument. It is recognized the way the
     * parser for the arguments recognizes it: it is the first element, unless
     * that element is an option.
     *
     * @param list<string> $argv
     *
     * @return list<string>
     */
    private function arguments(array $argv): array
    {
        if (isset($argv[0][0]) && $argv[0][0] !== '-') {
            array_shift($argv);
        }

        return $argv;
    }

    /**
     * @param non-empty-string $file
     *
     * @return ?non-empty-string
     */
    private function checksum(string $file): ?string
    {
        $checksum = @hash_file('sha256', $file);

        if ($checksum === false) {
            return null;
        }

        return $checksum;
    }
}
