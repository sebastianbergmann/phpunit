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

namespace PhpCsFixer\Linter;

use PhpCsFixer\Hasher;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CachingLinter implements LinterInterface
{
    private LinterInterface $sublinter;

    /**
     * @var array<string, LintingResultInterface>
     */
    private array $cache = [];

    public function __construct(LinterInterface $linter)
    {
        $this->sublinter = $linter;
    }

    public function isAsync(): bool
    {
        return $this->sublinter->isAsync();
    }

    public function lintFile(string $path): LintingResultInterface
    {
        $checksum = Hasher::calculate(file_get_contents($path));

        return $this->cache[$checksum] ??= $this->sublinter->lintFile($path);
    }

    public function lintSource(string $source): LintingResultInterface
    {
        $checksum = Hasher::calculate($source);

        return $this->cache[$checksum] ??= $this->sublinter->lintSource($source);
    }
}
