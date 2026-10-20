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

use PhpCsFixer\Tokenizer\Analyzer\Analysis\ArgumentAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FunctionsAnalyzer
{
    private const POSSIBLE_KINDS = [
        \T_DOUBLE_COLON, \T_FUNCTION, CT::T_NAMESPACE_OPERATOR, \T_NEW, CT::T_RETURN_REF, \T_STRING, \T_OBJECT_OPERATOR, FCT::T_NULLSAFE_OBJECT_OPERATOR, FCT::T_ATTRIBUTE];

    /**
     * @var array{tokens: string, imports: list<NamespaceUseAnalysis>, declarations: list<int>}
     */
    private array $functionsAnalysis = ['tokens' => '', 'imports' => [], 'declarations' => []];

    public function isGlobalFunctionCall(Tokens $tokens, int $index): bool
    {
        return $this->isGlobalFunctionCallOrUsage($tokens, $index, true);
    }

    public function isGlobalFunctionUsage(Tokens $tokens, int $index): bool
    {
        return $this->isGlobalFunctionCallOrUsage($tokens, $index, false);
    }

    /**
     * @return array<string, ArgumentAnalysis>
     */
    public function getFunctionArguments(Tokens $tokens, int $functionIndex): array
    {
        $argumentsStart = $tokens->getNextTokenOfKind($functionIndex, ['(']);
        $argumentsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $argumentsStart);
        $argumentAnalyzer = new ArgumentsAnalyzer();
        $arguments = [];

        foreach ($argumentAnalyzer->getArguments($tokens, $argumentsStart, $argumentsEnd) as $start => $end) {
            $argumentInfo = $argumentAnalyzer->getArgumentInfo($tokens, $start, $end);
            $arguments[$argumentInfo->getName()] = $argumentInfo;
        }

        return $arguments;
    }

    public function getFunctionReturnType(Tokens $tokens, int $methodIndex): ?TypeAnalysis
    {
        $argumentsStart = $tokens->getNextTokenOfKind($methodIndex, ['(']);
        $argumentsEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $argumentsStart);
        $typeColonIndex = $tokens->getNextMeaningfulToken($argumentsEnd);

        if ($tokens[$typeColonIndex]->isGivenKind(CT::T_USE_LAMBDA)) {
            $lambdaUseStart = $tokens->getNextTokenOfKind($typeColonIndex, ['(']);
            $lambdaUseEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $lambdaUseStart);
            $typeColonIndex = $tokens->getNextMeaningfulToken($lambdaUseEnd);
        }

        if (!$tokens[$typeColonIndex]->isGivenKind(CT::T_TYPE_COLON)) {
            return null;
        }

        $type = '';
        $typeStartIndex = $tokens->getNextMeaningfulToken($typeColonIndex);
        $typeEndIndex = $typeStartIndex;
        $functionBodyStart = $tokens->getNextTokenOfKind($typeColonIndex, ['{', ';', [\T_DOUBLE_ARROW]]);

        for ($i = $typeStartIndex; $i < $functionBodyStart; ++$i) {
            if ($tokens[$i]->isWhitespace() || $tokens[$i]->isComment()) {
                continue;
            }

            $type .= $tokens[$i]->getContent();
            $typeEndIndex = $i;
        }

        return new TypeAnalysis($type, $typeStartIndex, $typeEndIndex);
    }

    public function isTheSameClassCall(Tokens $tokens, int $index): bool
    {
        if (!$tokens->offsetExists($index)) {
            throw new \InvalidArgumentException(\sprintf('Token index %d does not exist.', $index));
        }

        $operatorIndex = $tokens->getPrevMeaningfulToken($index);

        if (null === $operatorIndex) {
            return false;
        }

        if (!$tokens[$operatorIndex]->isObjectOperator() && !$tokens[$operatorIndex]->isGivenKind(\T_DOUBLE_COLON)) {
            return false;
        }

        $referenceIndex = $tokens->getPrevMeaningfulToken($operatorIndex);

        if (null === $referenceIndex) {
            return false;
        }

        if (!$tokens[$referenceIndex]->equalsAny([[\T_VARIABLE, '$this'], [\T_STRING, 'self'], [\T_STATIC, 'static']], false)) {
            return false;
        }

        return $tokens[$tokens->getNextMeaningfulToken($index)]->equals('(');
    }

    /**
     * Important: risky because of the limited (file) scope of the tool.
     */
    private function isGlobalFunctionCallOrUsage(Tokens $tokens, int $index, bool $callOnly): bool
    {
        if (!$tokens[$index]->isGivenKind(\T_STRING)) {
            return false;
        }

        $openParenthesisIndex = $tokens->getNextMeaningfulToken($index);

        if (!$tokens[$openParenthesisIndex]->equals('(')) {
            return false;
        }

        $previousIsNamespaceSeparator = false;
        $prevIndex = $tokens->getPrevMeaningfulToken($index);

        if ($tokens[$prevIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $previousIsNamespaceSeparator = true;
            $prevIndex = $tokens->getPrevMeaningfulToken($prevIndex);
        }

        if ($tokens[$prevIndex]->isGivenKind(self::POSSIBLE_KINDS)) {
            return false;
        }

        if ($callOnly && $tokens[$tokens->getNextMeaningfulToken($openParenthesisIndex)]->isGivenKind(CT::T_FIRST_CLASS_CALLABLE)) {
            return false;
        }

        if ($previousIsNamespaceSeparator) {
            return true;
        }

        $functionName = strtolower($tokens[$index]->getContent());

        if ('set' === $functionName) {
            if (!$tokens[$prevIndex]->equalsAny([[CT::T_PROPERTY_HOOK_BRACE_OPEN], ';', '}'])) {
                return true;
            }
            $closeParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesisIndex);
            $afterCloseParenthesisIndex = $tokens->getNextMeaningfulToken($closeParenthesisIndex);
            if ($tokens[$afterCloseParenthesisIndex]->equalsAny(['{', [\T_DOUBLE_ARROW]])) {
                return false;
            }
        }

        if ($tokens->isChanged() || $tokens->getCodeHash() !== $this->functionsAnalysis['tokens']) {
            $this->buildFunctionsAnalysis($tokens);
        }

        // figure out in which namespace we are
        $scopeStartIndex = 0;
        $scopeEndIndex = \count($tokens) - 1;
        $inGlobalNamespace = false;

        foreach ($tokens->getNamespaceDeclarations() as $declaration) {
            $scopeStartIndex = $declaration->getScopeStartIndex();
            $scopeEndIndex = $declaration->getScopeEndIndex();

            if ($index >= $scopeStartIndex && $index <= $scopeEndIndex) {
                $inGlobalNamespace = $declaration->isGlobalNamespace();

                break;
            }
        }

        // check if the call is to a function declared in the same namespace as the call is done,
        // if the call is already in the global namespace than declared functions are in the same
        // global namespace and don't need checking

        if (!$inGlobalNamespace) {
            foreach ($this->functionsAnalysis['declarations'] as $functionNameIndex) {
                if ($functionNameIndex < $scopeStartIndex || $functionNameIndex > $scopeEndIndex) {
                    continue;
                }

                if (strtolower($tokens[$functionNameIndex]->getContent()) === $functionName) {
                    return false;
                }
            }
        }

        foreach ($this->functionsAnalysis['imports'] as $functionUse) {
            if ($functionUse->getStartIndex() < $scopeStartIndex || $functionUse->getEndIndex() > $scopeEndIndex) {
                continue;
            }

            if ($functionName !== strtolower($functionUse->getShortName())) {
                continue;
            }

            // global import like `use function \str_repeat;`
            return $functionUse->getShortName() === ltrim($functionUse->getFullName(), '\\');
        }

        if (AttributeAnalyzer::isAttribute($tokens, $index)) {
            return false;
        }

        return true;
    }

    private function buildFunctionsAnalysis(Tokens $tokens): void
    {
        $this->functionsAnalysis = [
            'tokens' => $tokens->getCodeHash(),
            'imports' => [],
            'declarations' => [],
        ];

        // find declarations

        if ($tokens->isTokenKindFound(\T_FUNCTION)) {
            $end = \count($tokens);

            for ($i = 0; $i < $end; ++$i) {
                // skip classy, we are looking for functions not methods
                if ($tokens[$i]->isGivenKind(Token::getClassyTokenKinds())) {
                    $i = $tokens->getNextTokenOfKind($i, ['(', '{']);

                    if ($tokens[$i]->equals('(')) { // anonymous class
                        $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $i);
                        $i = $tokens->getNextTokenOfKind($i, ['{']);
                    }

                    $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_BRACE, $i);

                    continue;
                }

                if (!$tokens[$i]->isGivenKind(\T_FUNCTION)) {
                    continue;
                }

                $i = $tokens->getNextMeaningfulToken($i);

                if ($tokens[$i]->isGivenKind(CT::T_RETURN_REF)) {
                    $i = $tokens->getNextMeaningfulToken($i);
                }

                if (!$tokens[$i]->isGivenKind(\T_STRING)) {
                    continue;
                }

                $this->functionsAnalysis['declarations'][] = $i;
            }
        }

        // find imported functions

        $namespaceUsesAnalyzer = new NamespaceUsesAnalyzer();

        if ($tokens->isTokenKindFound(CT::T_FUNCTION_IMPORT)) {
            $declarations = $namespaceUsesAnalyzer->getDeclarationsFromTokens($tokens);

            foreach ($declarations as $declaration) {
                if ($declaration->isFunction()) {
                    $this->functionsAnalysis['imports'][] = $declaration;
                }
            }
        }
    }
}
