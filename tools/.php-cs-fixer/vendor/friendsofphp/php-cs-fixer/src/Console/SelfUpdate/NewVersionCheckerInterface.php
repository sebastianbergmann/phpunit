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

namespace PhpCsFixer\Console\SelfUpdate;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface NewVersionCheckerInterface
{
    /**
     * Returns the tag of the latest version.
     */
    public function getLatestVersion(): string;

    /**
     * Returns the tag of the latest minor/patch version of the given major version.
     */
    public function getLatestVersionOfMajor(int $majorVersion): ?string;

    /**
     * Returns -1, 0, or 1 if the first version is respectively less than,
     * equal to, or greater than the second.
     */
    public function compareVersions(string $versionA, string $versionB): int;
}
