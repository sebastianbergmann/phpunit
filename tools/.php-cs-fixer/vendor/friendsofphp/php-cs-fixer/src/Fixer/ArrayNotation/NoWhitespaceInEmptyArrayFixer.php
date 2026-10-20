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

namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Jeremiasz Major <jrh.mjr@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoWhitespaceInEmptyArrayFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Empty arrays should not contain only whitespace.',
            [new CodeSample("<?php\n\n\$foo = [\n];\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(CT::T_ARRAY_BRACKET_OPEN);
    }

    /**
     * {@inheritdoc}
     *
     * Must run after ArraySyntaxFixer, NoEmptyCommentFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(CT::T_ARRAY_BRACKET_CLOSE)) {
                continue;
            }

            if ($tokens->getPrevNonWhitespace($index) !== $index - 2) {
                continue;
            }

            if (!$tokens[$index - 2]->isGivenKind(CT::T_ARRAY_BRACKET_OPEN)) {
                continue;
            }

            $tokens->clearAt($index - 1);
        }
    }
}
