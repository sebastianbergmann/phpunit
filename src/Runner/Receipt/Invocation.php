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

use function assert;
use DateTimeImmutable;

/**
 * The first part of the receipt of a test run: what is being run.
 *
 * It is known before the bootstrap script, the extensions, and the tests are
 * loaded, so that it can be written before any of them can end the process.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Invocation
{
    /**
     * @var non-empty-string
     */
    private string $phpunitVersion;

    /**
     * @var ?non-empty-string
     */
    private ?string $phar;

    /**
     * @var ?non-empty-string
     */
    private ?string $pharChecksum;

    /**
     * @var non-empty-string
     */
    private string $phpVersion;

    /**
     * @var list<string>
     */
    private array $arguments;
    private string $workingDirectory;

    /**
     * @var ?non-empty-string
     */
    private ?string $configurationFile;

    /**
     * @var ?non-empty-string
     */
    private ?string $configurationFileChecksum;

    /**
     * @var list<non-empty-string>
     */
    private array $bootstrapScripts;

    /**
     * @var list<non-empty-string>
     */
    private array $extensionClasses;
    private DateTimeImmutable $startTime;

    /**
     * @param non-empty-string       $phpunitVersion
     * @param ?non-empty-string      $phar
     * @param ?non-empty-string      $pharChecksum
     * @param non-empty-string       $phpVersion
     * @param list<string>           $arguments
     * @param ?non-empty-string      $configurationFile
     * @param ?non-empty-string      $configurationFileChecksum
     * @param list<non-empty-string> $bootstrapScripts
     * @param list<non-empty-string> $extensionClasses
     */
    public function __construct(string $phpunitVersion, ?string $phar, ?string $pharChecksum, string $phpVersion, array $arguments, string $workingDirectory, ?string $configurationFile, ?string $configurationFileChecksum, array $bootstrapScripts, array $extensionClasses, DateTimeImmutable $startTime)
    {
        $this->phpunitVersion            = $phpunitVersion;
        $this->phar                      = $phar;
        $this->pharChecksum              = $pharChecksum;
        $this->phpVersion                = $phpVersion;
        $this->arguments                 = $arguments;
        $this->workingDirectory          = $workingDirectory;
        $this->configurationFile         = $configurationFile;
        $this->configurationFileChecksum = $configurationFileChecksum;
        $this->bootstrapScripts          = $bootstrapScripts;
        $this->extensionClasses          = $extensionClasses;
        $this->startTime                 = $startTime;
    }

    /**
     * @return non-empty-string
     */
    public function phpunitVersion(): string
    {
        return $this->phpunitVersion;
    }

    /**
     * @phpstan-assert-if-true !null $this->phar
     */
    public function hasPhar(): bool
    {
        return $this->phar !== null;
    }

    /**
     * The PHAR that PHPUnit is run from.
     *
     * @return non-empty-string
     */
    public function phar(): string
    {
        assert($this->phar !== null);

        return $this->phar;
    }

    /**
     * @phpstan-assert-if-true !null $this->pharChecksum
     */
    public function hasPharChecksum(): bool
    {
        return $this->pharChecksum !== null;
    }

    /**
     * The SHA-256 checksum of the PHAR that PHPUnit is run from.
     *
     * @return non-empty-string
     */
    public function pharChecksum(): string
    {
        assert($this->pharChecksum !== null);

        return $this->pharChecksum;
    }

    /**
     * @return non-empty-string
     */
    public function phpVersion(): string
    {
        return $this->phpVersion;
    }

    /**
     * The arguments that PHPUnit was invoked with, without the name of the script.
     *
     * @return list<string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function workingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @phpstan-assert-if-true !null $this->configurationFile
     */
    public function hasConfigurationFile(): bool
    {
        return $this->configurationFile !== null;
    }

    /**
     * @return non-empty-string
     */
    public function configurationFile(): string
    {
        assert($this->configurationFile !== null);

        return $this->configurationFile;
    }

    /**
     * @phpstan-assert-if-true !null $this->configurationFileChecksum
     */
    public function hasConfigurationFileChecksum(): bool
    {
        return $this->configurationFileChecksum !== null;
    }

    /**
     * The SHA-256 checksum of the contents of the XML configuration file.
     *
     * @return non-empty-string
     */
    public function configurationFileChecksum(): string
    {
        assert($this->configurationFileChecksum !== null);

        return $this->configurationFileChecksum;
    }

    /**
     * The bootstrap script, followed by the bootstrap scripts of test suites.
     *
     * @return list<non-empty-string>
     */
    public function bootstrapScripts(): array
    {
        return $this->bootstrapScripts;
    }

    /**
     * The bootstrap classes of the extensions that are configured.
     *
     * @return list<non-empty-string>
     */
    public function extensionClasses(): array
    {
        return $this->extensionClasses;
    }

    public function startTime(): DateTimeImmutable
    {
        return $this->startTime;
    }
}
