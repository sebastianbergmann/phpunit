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

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FullyQualifiedNameAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\WhitespacesAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @internal
 *
 * @phpstan-require-implements FixerInterface
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
trait DocBlockAnnotationTrait
{
    final protected function getDocBlockIndex(Tokens $tokens, int $index): int
    {
        do {
            $index = $tokens->getPrevNonWhitespace($index);

            if ($tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
                $index = $tokens->getPrevTokenOfKind($index, [[\T_ATTRIBUTE]]);
            }
        } while ($tokens[$index]->isGivenKind([\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_FINAL, \T_ABSTRACT, \T_COMMENT, FCT::T_ATTRIBUTE, FCT::T_READONLY]));

        return $index;
    }

    /**
     * @param list<string>                     $preventingAnnotations
     * @param list<non-empty-lowercase-string> $preventingAttributes
     */
    final protected function ensureIsDocBlockWithAnnotation(
        Tokens $tokens,
        int $index,
        string $annotation,
        array $preventingAnnotations,
        array $preventingAttributes
    ): void {
        $docBlockIndex = $this->getDocBlockIndex($tokens, $index);

        if ($this->isPreventedByAttribute($tokens, $index, $preventingAttributes)) {
            return;
        }

        if ($tokens[$docBlockIndex]->isGivenKind(\T_DOC_COMMENT)) {
            $this->updateDocBlockIfNeeded($tokens, $docBlockIndex, $annotation, $preventingAnnotations);
        } else {
            $this->createDocBlock($tokens, $docBlockIndex, $annotation);
        }
    }

    protected function createDocBlock(Tokens $tokens, int $docBlockIndex, string $annotation): void
    {
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
        $toInsert = [
            new Token([\T_DOC_COMMENT, "/**{$lineEnd}{$originalIndent} * @{$annotation}{$lineEnd}{$originalIndent} */"]),
            new Token([\T_WHITESPACE, $lineEnd.$originalIndent]),
        ];
        $index = $tokens->getNextMeaningfulToken($docBlockIndex);
        $tokens->insertAt($index, $toInsert);

        if (!$tokens[$index - 1]->isGivenKind(\T_WHITESPACE)) {
            $extraNewLines = $this->whitespacesConfig->getLineEnding();

            if (!$tokens[$index - 1]->isGivenKind(\T_OPEN_TAG)) {
                $extraNewLines .= $this->whitespacesConfig->getLineEnding();
            }

            $tokens->insertAt($index, [
                new Token([\T_WHITESPACE, $extraNewLines.WhitespacesAnalyzer::detectIndent($tokens, $index)]),
            ]);
        }
    }

    /**
     * @param list<string> $preventingAnnotations
     */
    private function updateDocBlockIfNeeded(
        Tokens $tokens,
        int $docBlockIndex,
        string $annotation,
        array $preventingAnnotations
    ): void {
        $doc = new DocBlock($tokens[$docBlockIndex]->getContent());
        foreach ($preventingAnnotations as $preventingAnnotation) {
            if ([] !== $doc->getAnnotationsOfType($preventingAnnotation)) {
                return;
            }
        }
        $doc = $this->makeDocBlockMultiLineIfNeeded($doc, $tokens, $docBlockIndex, $annotation);

        $lines = $this->addAnnotation($doc, $tokens, $docBlockIndex, $annotation);
        $lines = implode('', $lines);

        $tokens->getNamespaceDeclarations();
        $tokens[$docBlockIndex] = new Token([\T_DOC_COMMENT, $lines]);
    }

    /**
     * @param list<lowercase-string> $preventingAttributes
     */
    private function isPreventedByAttribute(Tokens $tokens, int $index, array $preventingAttributes): bool
    {
        if ([] === $preventingAttributes) {
            return false;
        }

        do {
            $index = $tokens->getPrevMeaningfulToken($index);
        } while ($tokens[$index]->isGivenKind([\T_FINAL, FCT::T_READONLY]));
        if (!$tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
            return false;
        }
        $index = $tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);

        $fullyQualifiedNameAnalyzer = new FullyQualifiedNameAnalyzer($tokens);

        foreach (AttributeAnalyzer::collect($tokens, $index) as $attributeAnalysis) {
            foreach ($attributeAnalysis->getAttributes() as $attribute) {
                if (\in_array(strtolower($fullyQualifiedNameAnalyzer->getFullyQualifiedName($attribute['name'], $attribute['start'], NamespaceUseAnalysis::TYPE_CLASS)), $preventingAttributes, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return non-empty-list<Line>
     */
    private function addAnnotation(
        DocBlock $docBlock,
        Tokens $tokens,
        int $docBlockIndex,
        string $annotation
    ): array {
        $lines = $docBlock->getLines();
        $originalIndent = WhitespacesAnalyzer::detectIndent($tokens, $docBlockIndex);
        $lineEnd = $this->whitespacesConfig->getLineEnding();
        array_splice($lines, -1, 0, [new Line($originalIndent.' * @'.$annotation.$lineEnd)]);

        return $lines;
    }

    private function makeDocBlockMultiLineIfNeeded(
        DocBlock $doc,
        Tokens $tokens,
        int $docBlockIndex,
        string $annotation
    ): DocBlock {
        $lines = $doc->getLines();
        if (1 === \count($lines) && [] === $doc->getAnnotationsOfType($annotation)) {
            $indent = WhitespacesAnalyzer::detectIndent($tokens, $tokens->getNextNonWhitespace($docBlockIndex));
            $doc->makeMultiLine($indent, $this->whitespacesConfig->getLineEnding());

            return $doc;
        }

        return $doc;
    }
}
