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
final class NoShortBoolCastFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     *
     * Must run before CastSpacesFixer.
     */
    public function getPriority(): int
    {
        return -9;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Short cast `bool` using double exclamation mark should not be used.',
            [new CodeSample("<?php\n\$a = !!\$b;\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('!');
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 1; $index > 1; --$index) {
            if ($tokens[$index]->equals('!')) {
                $index = $this->fixShortCast($tokens, $index);
            }
        }
    }

    private function fixShortCast(Tokens $tokens, int $index): int
    {
        for ($i = $index - 1; $i > 1; --$i) {
            if ($tokens[$i]->equals('!')) {
                $this->fixShortCastToBoolCast($tokens, $i, $index);

                break;
            }

            if (!$tokens[$i]->isComment() && !$tokens[$i]->isWhitespace()) {
                break;
            }
        }

        return $i;
    }

    private function fixShortCastToBoolCast(Tokens $tokens, int $start, int $end): void
    {
        for (; $start <= $end; ++$start) {
            if (
                !$tokens[$start]->isComment()
                && !($tokens[$start]->isWhitespace() && $tokens[$start - 1]->isComment())
            ) {
                $tokens->clearAt($start);
            }
        }

        $tokens->insertAt($start, new Token([\T_BOOL_CAST, '(bool)']));
    }
}
