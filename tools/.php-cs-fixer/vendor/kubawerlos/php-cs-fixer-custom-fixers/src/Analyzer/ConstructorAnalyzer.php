<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Analyzer;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixerCustomFixers\Analyzer\Analysis\ConstructorAnalysis;

/**
 * @internal
 */
final class ConstructorAnalyzer
{
    public function findNonAbstractConstructor(Tokens $tokens, int $classIndex): ?ConstructorAnalysis
    {
        if (!$tokens[$classIndex]->isGivenKind(\T_CLASS)) {
            throw new \InvalidArgumentException(\sprintf('Index %d is not a class.', $classIndex));
        }

        $tokensAnalyzer = new TokensAnalyzer($tokens);

        foreach ($tokensAnalyzer->getClassyElements() as $index => $element) {
            if ($element['classIndex'] !== $classIndex) {
                continue;
            }

            if (!$this->isConstructor($tokens, $index, $element)) {
                continue;
            }

            $constructorAttributes = $tokensAnalyzer->getMethodAttributes($index);
            if ($constructorAttributes['abstract']) {
                return null;
            }

            return new ConstructorAnalysis($tokens, $index);
        }

        return null;
    }

    /**
     * @param array<string, int|string|Token> $element
     */
    private function isConstructor(Tokens $tokens, int $index, array $element): bool
    {
        if ($element['type'] !== 'method') {
            return false;
        }

        $functionNameIndex = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($functionNameIndex));

        return $tokens[$functionNameIndex]->equals([\T_STRING, '__construct'], false);
    }
}
