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

namespace PhpCsFixer\Fixer\CastNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoUnsetCastFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Variables must be set `null` instead of using `(unset)` casting.',
            [new CodeSample("<?php\n\$a = (unset) \$b;\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_UNSET_CAST);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 1; $index > 0; --$index) {
            if ($tokens[$index]->isGivenKind(\T_UNSET_CAST)) {
                $this->fixUnsetCast($tokens, $index);
            }
        }
    }

    private function fixUnsetCast(Tokens $tokens, int $index): void
    {
        $assignmentIndex = $tokens->getPrevMeaningfulToken($index);
        if (null === $assignmentIndex || !$tokens[$assignmentIndex]->equals('=')) {
            return;
        }

        $varIndex = $tokens->getNextMeaningfulToken($index);
        if (null === $varIndex || !$tokens[$varIndex]->isGivenKind(\T_VARIABLE)) {
            return;
        }

        $afterVar = $tokens->getNextMeaningfulToken($varIndex);
        if (null === $afterVar || !$tokens[$afterVar]->equalsAny([';', [\T_CLOSE_TAG]])) {
            return;
        }

        $nextIsWhiteSpace = $tokens[$assignmentIndex + 1]->isWhitespace();

        $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        $tokens->clearTokenAndMergeSurroundingWhitespace($varIndex);

        ++$assignmentIndex;
        if (!$nextIsWhiteSpace) {
            $tokens->insertAt($assignmentIndex, new Token([\T_WHITESPACE, ' ']));
        }

        ++$assignmentIndex;
        $tokens->insertAt($assignmentIndex, new Token([\T_STRING, 'null']));
    }
}
