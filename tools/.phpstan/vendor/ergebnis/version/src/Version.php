<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/version
 */

namespace Ergebnis\Version;

final class Version
{
    /**
     * @see https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
     * @see https://regex101.com/r/Ly7O1x/3/
     */
    private const REGEX = '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';
    private Major $major;
    private Minor $minor;
    private Patch $patch;
    private PreRelease $preRelease;
    private BuildMetaData $buildMetaData;

    private function __construct(
        Major $major,
        Minor $minor,
        Patch $patch,
        PreRelease $preRelease,
        BuildMetaData $buildMetaData
    ) {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->preRelease = $preRelease;
        $this->buildMetaData = $buildMetaData;
    }

    /**
     * @throws Exception\InvalidVersion
     */
    public static function fromString(string $value): self
    {
        if (1 !== \preg_match(self::REGEX, $value, $matches)) {
            throw Exception\InvalidVersion::fromString($value);
        }

        $preRelease = PreRelease::empty();

        if (
            \array_key_exists('prerelease', $matches)
            && '' !== $matches['prerelease']
        ) {
            $preRelease = PreRelease::fromString($matches['prerelease']);
        }

        $buildMetaData = BuildMetaData::empty();

        if (
            \array_key_exists('buildmetadata', $matches)
            && '' !== $matches['buildmetadata']
        ) {
            $buildMetaData = BuildMetaData::fromString($matches['buildmetadata']);
        }

        return new self(
            Major::fromString($matches['major']),
            Minor::fromString($matches['minor']),
            Patch::fromString($matches['patch']),
            $preRelease,
            $buildMetaData,
        );
    }

    public function toString(): string
    {
        /**
         * @see https://semver.org/#spec-item-2
         */
        $value = \sprintf(
            '%s.%s.%s',
            $this->major->toString(),
            $this->minor->toString(),
            $this->patch->toString(),
        );

        /**
         * @see https://semver.org/#spec-item-9
         */
        if (!$this->preRelease->equals(PreRelease::empty())) {
            $value = \sprintf(
                '%s-%s',
                $value,
                $this->preRelease->toString(),
            );
        }

        /**
         * @see https://semver.org/#spec-item-10
         */
        if (!$this->buildMetaData->equals(BuildMetaData::empty())) {
            $value = \sprintf(
                '%s+%s',
                $value,
                $this->buildMetaData->toString(),
            );
        }

        return $value;
    }

    /**
     * @see https://semver.org/#spec-item-8
     */
    public function bumpMajor(): self
    {
        return new self(
            $this->major->bump(),
            Minor::fromInt(0),
            Patch::fromInt(0),
            PreRelease::empty(),
            BuildMetaData::empty(),
        );
    }

    /**
     * @see https://semver.org/#spec-item-7
     */
    public function bumpMinor(): self
    {
        return new self(
            $this->major,
            $this->minor->bump(),
            Patch::fromInt(0),
            PreRelease::empty(),
            BuildMetaData::empty(),
        );
    }

    /**
     * @see https://semver.org/#spec-item-6
     */
    public function bumpPatch(): self
    {
        return new self(
            $this->major,
            $this->minor,
            $this->patch->bump(),
            PreRelease::empty(),
            BuildMetaData::empty(),
        );
    }

    public function compare(self $other): int
    {
        $major = $this->major->compare($other->major);

        if (0 !== $major) {
            return $major;
        }

        $minor = $this->minor->compare($other->minor);

        if (0 !== $minor) {
            return $minor;
        }

        $patch = $this->patch->compare($other->patch);

        if (0 !== $patch) {
            return $patch;
        }

        if ($this->preRelease->equals(PreRelease::empty())) {
            if ($other->preRelease->equals(PreRelease::empty())) {
                return 0;
            }

            return 1;
        }

        if ($other->preRelease->equals(PreRelease::empty())) {
            return -1;
        }

        $preRelease = $this->preRelease->compare($other->preRelease);

        if (0 !== $preRelease) {
            return $preRelease;
        }

        return 0;
    }

    public function isSmallerThan(self $other): bool
    {
        return -1 === $this->compare($other);
    }

    public function equals(self $other): bool
    {
        return 0 === $this->compare($other);
    }

    public function isGreaterThan(self $other): bool
    {
        return 1 === $this->compare($other);
    }

    public function major(): Major
    {
        return $this->major;
    }

    public function minor(): Minor
    {
        return $this->minor;
    }

    public function patch(): Patch
    {
        return $this->patch;
    }

    public function preRelease(): PreRelease
    {
        return $this->preRelease;
    }

    public function buildMetaData(): BuildMetaData
    {
        return $this->buildMetaData;
    }
}
