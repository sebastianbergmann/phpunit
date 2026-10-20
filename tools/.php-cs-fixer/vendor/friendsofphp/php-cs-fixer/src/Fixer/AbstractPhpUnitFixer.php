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

namespace PhpCsFixer\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FullyQualifiedNameAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\PhpUnitTestCaseAnalyzer;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractPhpUnitFixer extends AbstractFixer
{
    use DocBlockAnnotationTrait;

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_STRING]);
    }

    final protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ((new PhpUnitTestCaseAnalyzer())->findPhpUnitClasses($tokens) as $indices) {
            $this->applyPhpUnitClassFix($tokens, $indices[0], $indices[1]);
        }
    }

    abstract protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void;

    /**
     * @return iterable<array{
     *     index: int,
     *     loweredName: string,
     *     openBraceIndex: int,
     *     closeBraceIndex: int,
     * }>
     */
    protected function getPreviousAssertCall(Tokens $tokens, int $startIndex, int $endIndex): iterable
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        for ($index = $endIndex; $index > $startIndex; --$index) {
            $index = $tokens->getPrevTokenOfKind($index, [[\T_STRING]]);

            // test if "assert" something call
            $loweredContent = strtolower($tokens[$index]->getContent());

            if (!str_starts_with($loweredContent, 'assert')) {
                continue;
            }

            // test candidate for simple calls like: ([\]+'some fixable call'(...))
            $openBraceIndex = $tokens->getNextMeaningfulToken($index);

            if (!$tokens[$openBraceIndex]->equals('(')) {
                continue;
            }

            if (!$functionsAnalyzer->isTheSameClassCall($tokens, $index)) {
                continue;
            }

            yield [
                'index' => $index,
                'loweredName' => $loweredContent,
                'openBraceIndex' => $openBraceIndex,
                'closeBraceIndex' => $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openBraceIndex),
            ];
        }
    }

    final protected function isTestAttributePresent(Tokens $tokens, int $index): bool
    {
        $attributeIndex = $tokens->getPrevTokenOfKind($index, ['{', [FCT::T_ATTRIBUTE]]);
        if (!$tokens[$attributeIndex]->isGivenKind(FCT::T_ATTRIBUTE)) {
            return false;
        }

        $fullyQualifiedNameAnalyzer = new FullyQualifiedNameAnalyzer($tokens);

        foreach (AttributeAnalyzer::collect($tokens, $attributeIndex) as $attributeAnalysis) {
            foreach ($attributeAnalysis->getAttributes() as $attribute) {
                $attributeName = strtolower($fullyQualifiedNameAnalyzer->getFullyQualifiedName($attribute['name'], $attribute['start'], NamespaceUseAnalysis::TYPE_CLASS));
                if ('phpunit\framework\attributes\test' === $attributeName) {
                    return true;
                }
            }
        }

        return false;
    }
}
