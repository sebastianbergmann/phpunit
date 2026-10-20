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

/**
 * Handle PHP code linting process.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Linter implements LinterInterface
{
    private LinterInterface $subLinter;

    public function __construct()
    {
        $this->subLinter = new TokenizerLinter();
    }

    public function isAsync(): bool
    {
        return $this->subLinter->isAsync();
    }

    public function lintFile(string $path): LintingResultInterface
    {
        return $this->subLinter->lintFile($path);
    }

    public function lintSource(string $source): LintingResultInterface
    {
        return $this->subLinter->lintSource($source);
    }
}
