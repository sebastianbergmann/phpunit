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

use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NamespacesAnalyzer
{
    /**
     * @return list<NamespaceAnalysis>
     */
    public function getDeclarations(Tokens $tokens): array
    {
        $namespaces = [];

        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];

            if (!$token->isGivenKind(\T_NAMESPACE)) {
                continue;
            }

            $declarationEndIndex = $tokens->getNextTokenOfKind($index, [';', '{']);
            $namespace = trim($tokens->generatePartialCode($index + 1, $declarationEndIndex - 1));
            $declarationParts = explode('\\', $namespace);
            $shortName = end($declarationParts);

            if ($tokens[$declarationEndIndex]->equals('{')) {
                $scopeEndIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $declarationEndIndex);
            } else {
                $scopeEndIndex = $tokens->getNextTokenOfKind($declarationEndIndex, [[\T_NAMESPACE]]);
                if (null === $scopeEndIndex) {
                    $scopeEndIndex = \count($tokens);
                }
                --$scopeEndIndex;
            }

            $namespaces[] = new NamespaceAnalysis(
                $namespace,
                $shortName,
                $index,
                $declarationEndIndex,
                $index,
                $scopeEndIndex,
            );

            // Continue the analysis after the end of this namespace to find the next one
            $index = $scopeEndIndex;
        }

        if (0 === \count($namespaces) && $tokens->isTokenKindFound(\T_OPEN_TAG)) {
            $namespaces[] = new NamespaceAnalysis(
                '',
                '',
                $openTagIndex = $tokens[0]->isGivenKind(\T_INLINE_HTML) ? 1 : 0,
                $openTagIndex,
                $openTagIndex,
                \count($tokens) - 1,
            );
        }

        return $namespaces;
    }

    public function getNamespaceAt(Tokens $tokens, int $index): NamespaceAnalysis
    {
        if (!$tokens->offsetExists($index)) {
            throw new \InvalidArgumentException(\sprintf('Token index %d does not exist.', $index));
        }

        foreach ($this->getDeclarations($tokens) as $namespace) {
            if ($namespace->getScopeStartIndex() <= $index && $namespace->getScopeEndIndex() >= $index) {
                return $namespace;
            }
        }

        throw new \LogicException(\sprintf('Unable to get the namespace at index %d.', $index));
    }

    /**
     * @return array{NamespaceAnalysis, array<string, NamespaceUseAnalysis>}
     */
    public static function collectNamespaceAnalysis(Tokens $tokens, int $startIndex): array
    {
        $namespaceAnalysis = (new self())->getNamespaceAt($tokens, $startIndex);
        $namespaceUseAnalyses = (new NamespaceUsesAnalyzer())->getDeclarationsInNamespace($tokens, $namespaceAnalysis);

        $uses = [];
        foreach ($namespaceUseAnalyses as $use) {
            if (!$use->isClass()) {
                continue;
            }

            $uses[$use->getShortName()] = $use;
        }

        return [$namespaceAnalysis, $uses];
    }
}
