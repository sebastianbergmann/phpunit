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

namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUnneededImportAliasFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Imports should not be aliased as the same name.',
            [new CodeSample("<?php\nuse A\\B\\Foo as Foo;\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_USE, \T_AS]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoSinglelineWhitespaceBeforeSemicolonsFixer.
     * Must run after PhpUnitNamespacedFixer.
     */
    public function getPriority(): int
    {
        return 1;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 1; 0 <= $index; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_AS)) {
                continue;
            }

            $aliasIndex = $tokens->getNextMeaningfulToken($index);

            if (!$tokens[$aliasIndex]->isGivenKind(\T_STRING)) {
                continue;
            }

            $importIndex = $tokens->getPrevMeaningfulToken($index);

            if (!$tokens[$importIndex]->isGivenKind(\T_STRING)) {
                continue;
            }

            if ($tokens[$importIndex]->getContent() !== $tokens[$aliasIndex]->getContent()) {
                continue;
            }

            do {
                $importIndex = $tokens->getPrevMeaningfulToken($importIndex);
            } while ($tokens[$importIndex]->isGivenKind([\T_NS_SEPARATOR, \T_STRING, \T_AS]) || $tokens[$importIndex]->equals(','));

            if ($tokens[$importIndex]->isGivenKind([CT::T_FUNCTION_IMPORT, CT::T_CONST_IMPORT])) {
                $importIndex = $tokens->getPrevMeaningfulToken($importIndex);
            }

            if (!$tokens[$importIndex]->isGivenKind([\T_USE, CT::T_GROUP_IMPORT_BRACE_OPEN])) {
                continue;
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($aliasIndex);
            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        }
    }
}
