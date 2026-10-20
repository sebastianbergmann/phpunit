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

use PhpCsFixer\FileReader;
use PhpCsFixer\Hasher;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Handle PHP code linting.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TokenizerLinter implements LinterInterface
{
    public function isAsync(): bool
    {
        return false;
    }

    public function lintFile(string $path): LintingResultInterface
    {
        return $this->lintSource(FileReader::createSingleton()->read($path));
    }

    public function lintSource(string $source): LintingResultInterface
    {
        try {
            // To lint, we will parse the source into Tokens.
            // During that process, it might throw a ParseError or CompileError.
            // If it won't, cache of tokenized version of source will be kept, which is great for Runner.
            // Yet, first we need to clear already existing cache to not hit it and lint the code indeed.
            $codeHash = Hasher::calculate($source);
            Tokens::clearCache($codeHash);
            Tokens::fromCode($source);

            return new TokenizerLintingResult();
        } catch (\CompileError|\ParseError $e) {
            return new TokenizerLintingResult($e);
        }
    }
}
