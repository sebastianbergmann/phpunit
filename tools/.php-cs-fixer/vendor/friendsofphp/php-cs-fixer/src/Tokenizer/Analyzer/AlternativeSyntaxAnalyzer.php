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

namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @TODO 4.0 remove this analyzer and move this logic into a transformer
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AlternativeSyntaxAnalyzer
{
    private const ALTERNATIVE_SYNTAX_BLOCK_EDGES = [
        \T_IF => [\T_ENDIF, \T_ELSE, \T_ELSEIF],
        \T_ELSE => [\T_ENDIF],
        \T_ELSEIF => [\T_ENDIF, \T_ELSE, \T_ELSEIF],
        \T_FOR => [\T_ENDFOR],
        \T_FOREACH => [\T_ENDFOREACH],
        \T_WHILE => [\T_ENDWHILE],
        \T_SWITCH => [\T_ENDSWITCH],
    ];

    public function belongsToAlternativeSyntax(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equals(':')) {
            return false;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);

        if ($tokens[$prevIndex]->isGivenKind(\T_ELSE)) {
            return true;
        }

        if (!$tokens[$prevIndex]->equals(')')) {
            return false;
        }

        $openParenthesisIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_PARENTHESIS, $prevIndex);
        $beforeOpenParenthesisIndex = $tokens->getPrevMeaningfulToken($openParenthesisIndex);

        return $tokens[$beforeOpenParenthesisIndex]->isGivenKind([
            \T_DECLARE,
            \T_ELSEIF,
            \T_FOR,
            \T_FOREACH,
            \T_IF,
            \T_SWITCH,
            \T_WHILE,
        ]);
    }

    public function findAlternativeSyntaxBlockEnd(Tokens $tokens, int $index): int
    {
        if (!isset($tokens[$index])) {
            throw new \InvalidArgumentException("There is no token at index {$index}.");
        }

        if (!$this->isStartOfAlternativeSyntaxBlock($tokens, $index)) {
            throw new \InvalidArgumentException("Token at index {$index} is not the start of an alternative syntax block.");
        }

        $startTokenKind = $tokens[$index]->getId();

        if (!isset(self::ALTERNATIVE_SYNTAX_BLOCK_EDGES[$startTokenKind])) {
            throw new \LogicException(\sprintf('Unknown startTokenKind: %s', $tokens[$index]->toJson()));
        }

        $endTokenKinds = self::ALTERNATIVE_SYNTAX_BLOCK_EDGES[$startTokenKind];

        $findKinds = [[$startTokenKind]];
        foreach ($endTokenKinds as $endTokenKind) {
            $findKinds[] = [$endTokenKind];
        }

        while (true) {
            $index = $tokens->getNextTokenOfKind($index, $findKinds);

            if ($tokens[$index]->isGivenKind($endTokenKinds)) {
                return $index;
            }

            if ($this->isStartOfAlternativeSyntaxBlock($tokens, $index)) {
                $index = $this->findAlternativeSyntaxBlockEnd($tokens, $index);
            }
        }
    }

    private function isStartOfAlternativeSyntaxBlock(Tokens $tokens, int $index): bool
    {
        $map = self::ALTERNATIVE_SYNTAX_BLOCK_EDGES;
        $startTokenKind = $tokens[$index]->getId();

        if (null === $startTokenKind || !isset($map[$startTokenKind])) {
            return false;
        }

        $index = $tokens->getNextMeaningfulToken($index);

        if ($tokens[$index]->equals('(')) {
            $index = $tokens->getNextMeaningfulToken(
                $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index),
            );
        }

        return $tokens[$index]->equals(':');
    }
}
