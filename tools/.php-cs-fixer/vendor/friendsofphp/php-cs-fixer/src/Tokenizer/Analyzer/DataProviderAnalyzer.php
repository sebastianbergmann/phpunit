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

use PhpCsFixer\DocBlock\TypeExpression;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\DataProviderAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @internal
 *
 * @phpstan-import-type _AttributeItem from \PhpCsFixer\Tokenizer\Analyzer\Analysis\AttributeAnalysis
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DataProviderAnalyzer
{
    private const REGEX_CLASS = '(?:\\\?+'.TypeExpression::REGEX_IDENTIFIER
        .'(\\\\'.TypeExpression::REGEX_IDENTIFIER.')*+)';

    /**
     * @return list<DataProviderAnalysis>
     */
    public function getDataProviders(Tokens $tokens, int $startIndex, int $endIndex): array
    {
        $fullyQualifiedNameAnalyzer = new FullyQualifiedNameAnalyzer($tokens);

        $methods = $this->getMethods($tokens, $startIndex, $endIndex);

        $dataProviders = [];
        foreach ($methods as $methodIndex) {
            [$attributeIndex, $docCommentIndex] = $this->getAttributeIndexAndDocCommentIndices($tokens, $methodIndex);

            if (null !== $attributeIndex) {
                foreach (AttributeAnalyzer::collect($tokens, $attributeIndex) as $attributeAnalysis) {
                    foreach ($attributeAnalysis->getAttributes() as $attribute) {
                        $dataProviderNameIndex = $this->getDataProviderNameIndex($tokens, $fullyQualifiedNameAnalyzer, $attribute);
                        if (null === $dataProviderNameIndex) {
                            continue;
                        }
                        $dataProviders[substr($tokens[$dataProviderNameIndex]->getContent(), 1, -1)][] = [$dataProviderNameIndex, 0];
                    }
                }
            }

            if (null !== $docCommentIndex) {
                Preg::matchAll(
                    '/@dataProvider\h+(('.self::REGEX_CLASS.'::)?'.TypeExpression::REGEX_IDENTIFIER.')/',
                    $tokens[$docCommentIndex]->getContent(),
                    $matches,
                    \PREG_OFFSET_CAPTURE,
                );

                foreach ($matches[1] as $k => [$matchName]) {
                    \assert(isset($matches[0][$k]));

                    $dataProviders[$matchName][] = [$docCommentIndex, $matches[0][$k][1]];
                }
            }
        }

        $dataProviderAnalyses = [];
        foreach ($dataProviders as $dataProviderName => $dataProviderUsages) {
            $lowercaseDataProviderName = strtolower($dataProviderName);
            if (!\array_key_exists($lowercaseDataProviderName, $methods)) {
                continue;
            }
            $dataProviderAnalyses[$methods[$lowercaseDataProviderName]] = new DataProviderAnalysis(
                $tokens[$methods[$lowercaseDataProviderName]]->getContent(),
                $methods[$lowercaseDataProviderName],
                $dataProviderUsages,
            );
        }

        ksort($dataProviderAnalyses);

        return array_values($dataProviderAnalyses);
    }

    /**
     * @return array<string, int>
     */
    private function getMethods(Tokens $tokens, int $startIndex, int $endIndex): array
    {
        $functions = [];
        for ($index = $startIndex; $index < $endIndex; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_FUNCTION)) {
                continue;
            }

            $functionNameIndex = $tokens->getNextNonWhitespace($index);

            if (!$tokens[$functionNameIndex]->isGivenKind(\T_STRING)) {
                continue;
            }

            $functions[strtolower($tokens[$functionNameIndex]->getContent())] = $functionNameIndex;
        }

        return $functions;
    }

    /**
     * @return array{null|int, null|int}
     */
    private function getAttributeIndexAndDocCommentIndices(Tokens $tokens, int $index): array
    {
        $attributeIndex = null;
        $docCommentIndex = null;
        while (!$tokens[$index]->equalsAny([';', '{', '}', [\T_OPEN_TAG]])) {
            --$index;

            if ($tokens[$index]->isGivenKind(FCT::T_ATTRIBUTE)) {
                $attributeIndex = $index;
            } elseif ($tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                $docCommentIndex = $index;
            }
        }

        return [$attributeIndex, $docCommentIndex];
    }

    /**
     * @param _AttributeItem $attribute
     */
    private function getDataProviderNameIndex(Tokens $tokens, FullyQualifiedNameAnalyzer $fullyQualifiedNameAnalyzer, array $attribute): ?int
    {
        $fullyQualifiedName = $fullyQualifiedNameAnalyzer->getFullyQualifiedName(
            $attribute['name'],
            $tokens->getNextMeaningfulToken($attribute['start']),
            NamespaceUseAnalysis::TYPE_CLASS,
        );

        // note: do not apply `DataProvider::class` here, as it would confuse `composer-dependency-analyser` to have PHPUnit as non-dev dependency
        if ('PHPUnit\Framework\Attributes\DataProvider' !== $fullyQualifiedName) {
            return null;
        }

        $closeParenthesisIndex = $tokens->getPrevTokenOfKind($attribute['end'] + 1, [')', [\T_ATTRIBUTE]]);
        if ($tokens[$closeParenthesisIndex]->isGivenKind(\T_ATTRIBUTE)) {
            return null;
        }

        $dataProviderNameIndex = $tokens->getPrevMeaningfulToken($closeParenthesisIndex);
        if (!$tokens[$dataProviderNameIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            return null;
        }

        $openParenthesisIndex = $tokens->getPrevMeaningfulToken($dataProviderNameIndex);
        if (!$tokens[$openParenthesisIndex]->equals('(')) {
            return null;
        }

        return $dataProviderNameIndex;
    }
}
