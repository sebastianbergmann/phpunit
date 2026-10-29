<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Documentation;

use Composer\InstalledVersions;
use Ergebnis\Version;

/**
 * @internal
 */
final class Reference
{
    private const PACKAGE_NAME = 'ergebnis/phpstan-rules';
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromPrettyVersion(string $prettyVersion): self
    {
        try {
            $version = Version\Version::fromString($prettyVersion);
        } catch (Version\Exception\InvalidVersion $exception) {
            return new self('main');
        }

        return new self($version->toString());
    }

    public static function fromVersion(Version\Version $version): self
    {
        return new self($version->toString());
    }

    public static function main(): self
    {
        return new self('main');
    }

    public static function installed(): self
    {
        $prettyVersion = '';

        if (InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            $prettyVersion = (string) InstalledVersions::getPrettyVersion(self::PACKAGE_NAME);
        }

        return self::fromPrettyVersion($prettyVersion);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
