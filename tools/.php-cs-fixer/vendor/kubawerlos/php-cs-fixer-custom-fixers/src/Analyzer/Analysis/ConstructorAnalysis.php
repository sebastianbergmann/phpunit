<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Analyzer\Analysis;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 */
final class ConstructorAnalysis
{
    private Tokens $tokens;
    private int $constructorIndex;

    public function __construct(Tokens $tokens, int $constructorIndex)
    {
        $this->tokens = $tokens;
        $this->constructorIndex = $constructorIndex;
    }

    public function getConstructorIndex(): int
    {
        return $this->constructorIndex;
    }

    /**
     * @return list<string>
     */
    public function getConstructorParameterNames(): array
    {
        $openParenthesis = $this->tokens->getNextTokenOfKind($this->constructorIndex, ['(']);
        \assert(\is_int($openParenthesis));
        $closeParenthesis = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis);

        $constructorParameterNames = [];
        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {
            if (!$this->tokens[$index]->isGivenKind(\T_VARIABLE)) {
                continue;
            }

            $constructorParameterNames[] = $this->tokens[$index]->getContent();
        }

        return $constructorParameterNames;
    }

    /**
     * @return array<int, string>
     */
    public function getConstructorPromotableParameters(): array
    {
        $openParenthesis = $this->tokens->getNextTokenOfKind($this->constructorIndex, ['(']);
        \assert(\is_int($openParenthesis));
        $closeParenthesis = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis);

        $constructorPromotableParameters = [];
        for ($index = $openParenthesis + 1; $index < $closeParenthesis; $index++) {
            if (!$this->tokens[$index]->isGivenKind(\T_VARIABLE)) {
                continue;
            }

            $typeIndex = $this->tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($typeIndex));
            if ($this->tokens[$typeIndex]->equalsAny(['(', ',', [\T_CALLABLE], [\T_ELLIPSIS]])) {
                continue;
            }

            $visibilityIndex = $this->tokens->getPrevTokenOfKind(
                $index,
                [
                    '(',
                    ',',
                    [CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE],
                    [CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED],
                    [CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC],
                ],
            );
            \assert(\is_int($visibilityIndex));
            if (!$this->tokens[$visibilityIndex]->equalsAny(['(', ','])) {
                continue;
            }

            $constructorPromotableParameters[$index] = $this->tokens[$index]->getContent();
        }

        return $constructorPromotableParameters;
    }

    /**
     * @return array<string, int>
     */
    public function getConstructorPromotableAssignments(): array
    {
        $openParenthesis = $this->tokens->getNextTokenOfKind($this->constructorIndex, ['(']);
        \assert(\is_int($openParenthesis));
        $closeParenthesis = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $openParenthesis);

        $openBrace = $this->tokens->getNextTokenOfKind($closeParenthesis, ['{']);
        \assert(\is_int($openBrace));
        $closeBrace = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $openBrace);

        $variables = [];
        $properties = [];
        $propertyToVariableMap = [];

        for ($index = $openBrace + 1; $index < $closeBrace; $index++) {
            if (!$this->tokens[$index]->isGivenKind(\T_VARIABLE)) {
                continue;
            }

            $semicolonIndex = $this->tokens->getNextMeaningfulToken($index);
            \assert(\is_int($semicolonIndex));
            if (!$this->tokens[$semicolonIndex]->equals(';')) {
                continue;
            }

            $propertyIndex = $this->getPropertyIndex($index, $openBrace);
            if ($propertyIndex === null) {
                continue;
            }

            $properties[$propertyIndex] = $this->tokens[$propertyIndex]->getContent();
            $variables[$index] = $this->tokens[$index]->getContent();
            $propertyToVariableMap[$propertyIndex] = $index;
        }

        foreach ($this->getDuplicatesIndices($properties) as $duplicate) {
            unset($variables[$propertyToVariableMap[$duplicate]]);
        }

        foreach ($this->getDuplicatesIndices($variables) as $duplicate) {
            unset($variables[$duplicate]);
        }

        return \array_flip($variables);
    }

    private function getPropertyIndex(int $index, int $openBrace): ?int
    {
        $assignmentIndex = $this->tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($assignmentIndex));
        if (!$this->tokens[$assignmentIndex]->equals('=')) {
            return null;
        }

        $propertyIndex = $this->tokens->getPrevMeaningfulToken($assignmentIndex);
        if (!$this->tokens[$propertyIndex]->isGivenKind(\T_STRING)) {
            return null;
        }
        \assert(\is_int($propertyIndex));

        $objectOperatorIndex = $this->tokens->getPrevMeaningfulToken($propertyIndex);
        \assert(\is_int($objectOperatorIndex));

        $thisIndex = $this->tokens->getPrevMeaningfulToken($objectOperatorIndex);
        \assert(\is_int($thisIndex));
        if (!$this->tokens[$thisIndex]->equals([\T_VARIABLE, '$this'])) {
            return null;
        }

        $prevThisIndex = $this->tokens->getPrevMeaningfulToken($thisIndex);
        \assert(\is_int($prevThisIndex));
        if ($prevThisIndex > $openBrace && !$this->tokens[$prevThisIndex]->equalsAny(['}', ';'])) {
            return null;
        }

        return $propertyIndex;
    }

    /**
     * @param array<int, string> $array
     *
     * @return array<int, int>
     */
    private function getDuplicatesIndices(array $array): array
    {
        $duplicates = [];
        $values = [];
        foreach ($array as $key => $value) {
            if (\array_key_exists($value, $values)) {
                \assert(\is_int($values[$value]));
                $duplicates[$values[$value]] = $values[$value];

                $duplicates[$key] = $key;
            }
            $values[$value] = $key;
        }

        return $duplicates;
    }
}
