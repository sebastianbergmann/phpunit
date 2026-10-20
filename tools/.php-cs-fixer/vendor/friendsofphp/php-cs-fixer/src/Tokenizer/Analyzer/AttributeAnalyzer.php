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

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\AttributeAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @phpstan-import-type _AttributeItems from AttributeAnalysis
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AttributeAnalyzer
{
    private const TOKEN_KINDS_NOT_ALLOWED_IN_ATTRIBUTE = [
        ';',
        '{',
        [\T_ATTRIBUTE],
        [\T_FUNCTION],
        [\T_OPEN_TAG],
        [\T_OPEN_TAG_WITH_ECHO],
        [\T_PRIVATE],
        [\T_PROTECTED],
        [\T_PUBLIC],
        [\T_RETURN],
        [\T_VARIABLE],
        [CT::T_ATTRIBUTE_CLOSE],
    ];

    /**
     * Check if given index is an attribute declaration.
     */
    public static function isAttribute(Tokens $tokens, int $index): bool
    {
        if (
            !$tokens[$index]->isGivenKind(\T_STRING) // checked token is not a string
            || !$tokens->isAnyTokenKindsFound([FCT::T_ATTRIBUTE]) // no attributes in the tokens collection
        ) {
            return false;
        }

        $attributeStartIndex = $tokens->getPrevTokenOfKind($index, self::TOKEN_KINDS_NOT_ALLOWED_IN_ATTRIBUTE);
        if (!$tokens[$attributeStartIndex]->isGivenKind(\T_ATTRIBUTE)) {
            return false;
        }

        // now, between attribute start and the attribute candidate index cannot be more "(" than ")"
        $count = 0;
        for ($i = $attributeStartIndex + 1; $i < $index; ++$i) {
            if ($tokens[$i]->equals('(')) {
                ++$count;
            } elseif ($tokens[$i]->equals(')')) {
                --$count;
            }
        }

        return 0 === $count;
    }

    /**
     * Find all consecutive elements that start with #[ and end with ] and the attributes inside.
     *
     * @return non-empty-list<AttributeAnalysis>
     */
    public static function collect(Tokens $tokens, int $index): array
    {
        if (!$tokens[$index]->isGivenKind(\T_ATTRIBUTE)) {
            throw new \InvalidArgumentException('Given index must point to an attribute.');
        }

        // Rewind to first attribute in group
        while ($tokens[$prevIndex = $tokens->getPrevMeaningfulToken($index)]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
            $index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $prevIndex);
        }

        $elements = [];

        $openingIndex = $index;
        do {
            $elements[] = $element = self::collectOne($tokens, $openingIndex);
            $openingIndex = $tokens->getNextMeaningfulToken($element->getEndIndex());
        } while ($tokens[$openingIndex]->isGivenKind(\T_ATTRIBUTE));

        return $elements;
    }

    /**
     * Find one element that starts with #[ and ends with ] and the attributes inside.
     */
    public static function collectOne(Tokens $tokens, int $index): AttributeAnalysis
    {
        if (!$tokens[$index]->isGivenKind(\T_ATTRIBUTE)) {
            throw new \InvalidArgumentException('Given index must point to an attribute.');
        }

        $startIndex = $index;
        $prevIndex = $tokens->getPrevMeaningfulToken($index);

        if ($tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
            // Include comments/PHPDoc if they are present
            $startIndex = $tokens->getNextNonWhitespace($prevIndex);
        }

        $closingIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
        $endIndex = $tokens->getNextNonWhitespace($closingIndex);

        return new AttributeAnalysis(
            $startIndex,
            $endIndex - 1,
            $index,
            $closingIndex,
            self::collectAttributes($tokens, $index, $closingIndex),
        );
    }

    public static function determineAttributeFullyQualifiedName(Tokens $tokens, string $name, int $index): string
    {
        if ('\\' === $name[0]) {
            return $name;
        }

        if (!$tokens[$index]->isGivenKind([\T_STRING, \T_NS_SEPARATOR])) {
            $index = $tokens->getNextTokenOfKind($index, [[\T_STRING], [\T_NS_SEPARATOR]]);
        }

        [$namespaceAnalysis, $namespaceUseAnalyses] = NamespacesAnalyzer::collectNamespaceAnalysis($tokens, $index);
        $namespace = $namespaceAnalysis->getFullName();
        $firstTokenOfName = $tokens[$index]->getContent();
        $namespaceUseAnalysis = $namespaceUseAnalyses[$firstTokenOfName] ?? false;

        if ($namespaceUseAnalysis instanceof NamespaceUseAnalysis) {
            $namespace = $namespaceUseAnalysis->getFullName();

            if ($name === $firstTokenOfName) {
                return $namespace;
            }

            $name = substr((string) strstr($name, '\\'), 1);
        }

        return $namespace.'\\'.$name;
    }

    /**
     * @return _AttributeItems
     */
    private static function collectAttributes(Tokens $tokens, int $index, int $closingIndex): array
    {
        $elements = [];

        do {
            $attributeStartIndex = $index + 1;

            $nameStartIndex = $tokens->getNextTokenOfKind($index, [[\T_STRING], [\T_NS_SEPARATOR]]);
            $index = $tokens->getNextTokenOfKind($attributeStartIndex, ['(', ',', [CT::T_ATTRIBUTE_CLOSE]]);
            $attributeName = $tokens->generatePartialCode($nameStartIndex, $tokens->getPrevMeaningfulToken($index));

            // Find closing parentheses, we need to do this in case there's a comma inside the parentheses
            if ($tokens[$index]->equals('(')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $index);
                $index = $tokens->getNextTokenOfKind($index, [',', [CT::T_ATTRIBUTE_CLOSE]]);
            }

            $elements[] = [
                'start' => $attributeStartIndex,
                'end' => $index - 1,
                'name' => $attributeName,
            ];

            $nextIndex = $index;

            // In case there's a comma right before T_ATTRIBUTE_CLOSE
            if ($nextIndex < $closingIndex) {
                $nextIndex = $tokens->getNextMeaningfulToken($index);
            }
        } while ($nextIndex < $closingIndex);

        // End last element at newline if it exists and there's no trailing comma
        --$index;
        while ($tokens[$index]->isWhitespace()) {
            if (Preg::match('/\R/', $tokens[$index]->getContent())) {
                $lastElementKey = array_key_last($elements);
                $elements[$lastElementKey]['end'] = $index - 1;

                break;
            }
            --$index;
        }

        return $elements;
    }
}
