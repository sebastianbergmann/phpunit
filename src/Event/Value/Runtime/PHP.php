<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Runtime;

use const PHP_EXTRA_VERSION;
use const PHP_MAJOR_VERSION;
use const PHP_MINOR_VERSION;
use const PHP_RELEASE_VERSION;
use const PHP_SAPI;
use const PHP_VERSION;
use const PHP_VERSION_ID;
use function array_merge;
use function get_loaded_extensions;
use function sort;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class PHP
{
    private readonly string $version;
    private readonly int $versionId;
    private readonly int $versionMajor;
    private readonly int $versionMinor;
    private readonly int $versionPatch;
    private readonly string $versionExtra;
    private readonly string $sapi;

    /**
     * @psalm-var list<string>
     */
    private readonly array $extensions;

    public function __construct()
    {
        $this->version      = PHP_VERSION;
        $this->versionId    = PHP_VERSION_ID;
        $this->versionMajor = PHP_MAJOR_VERSION;
        $this->versionMinor = PHP_MINOR_VERSION;
        $this->versionPatch = PHP_RELEASE_VERSION;
        $this->versionExtra = PHP_EXTRA_VERSION;
        $this->sapi         = PHP_SAPI;

        $extensions = array_merge(
            get_loaded_extensions(true),
            get_loaded_extensions()
        );

        sort($extensions);

        $this->extensions = $extensions;
    }

    public function asString(): string
    {
        return $this->version;
    }

    public function sapi(): string
    {
        return $this->sapi;
    }

    public function major(): int
    {
        return $this->versionMajor;
    }

    public function minor(): int
    {
        return $this->versionMinor;
    }

    public function patch(): int
    {
        return $this->versionPatch;
    }

    public function extra(): string
    {
        return $this->versionExtra;
    }

    public function id(): int
    {
        return $this->versionId;
    }

    /**
     * @psalm-return list<string>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }
}
