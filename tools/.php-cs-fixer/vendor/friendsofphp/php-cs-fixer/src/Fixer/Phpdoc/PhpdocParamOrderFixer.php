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

namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\TypeExpression;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Jonathan Gruber <gruberjonathan@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocParamOrderFixer extends AbstractFixer
{
    private const PARAM_TAG = 'param';

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return parent::getPriority();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Orders all `@param` annotations in DocBlocks according to method signature.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         * Annotations in wrong order
                         *
                         * @param int   $a
                         * @param Foo   $c
                         * @param array $b
                         */
                        function m($a, array $b, Foo $c) {}

                        PHP,
                ),
            ],
        );
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            // Check for function / closure token
            $nextFunctionToken = $tokens->getNextTokenOfKind($index, [[\T_FUNCTION], [\T_FN]]);
            if (null === $nextFunctionToken) {
                return;
            }

            // Find start index of param block (opening parenthesis)
            $paramBlockStart = $tokens->getNextTokenOfKind($index, ['(']);
            if (null === $paramBlockStart) {
                return;
            }

            $doc = new DocBlock($token->getContent());
            $paramAnnotations = $doc->getAnnotationsOfType(self::PARAM_TAG);

            if ([] === $paramAnnotations) {
                continue;
            }

            $paramNames = $this->getFunctionParamNames($tokens, $paramBlockStart);
            $doc = $this->rewriteDocBlock($doc, $paramNames, $paramAnnotations);

            $tokens[$index] = new Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }

    /**
     * @return list<Token>
     */
    private function getFunctionParamNames(Tokens $tokens, int $paramBlockStart): array
    {
        $paramBlockEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $paramBlockStart);

        $paramNames = [];
        for (
            $i = $tokens->getNextTokenOfKind($paramBlockStart, [[\T_VARIABLE]]);
            null !== $i && $i < $paramBlockEnd;
            $i = $tokens->getNextTokenOfKind($i, [[\T_VARIABLE]])
        ) {
            $paramNames[] = $tokens[$i];
        }

        return $paramNames;
    }

    /**
     * Overwrite the param annotations in order.
     *
     * @param list<Token>                $paramNames
     * @param non-empty-list<Annotation> $paramAnnotations
     */
    private function rewriteDocBlock(DocBlock $doc, array $paramNames, array $paramAnnotations): DocBlock
    {
        $orderedAnnotations = $this->sortParamAnnotations($paramNames, $paramAnnotations);
        $otherAnnotations = $this->getOtherAnnotationsBetweenParams($doc, $paramAnnotations);

        // Append annotations found between param ones
        if ([] !== $otherAnnotations) {
            array_push($orderedAnnotations, ...$otherAnnotations);
        }

        // Overwrite all annotations between first and last @param tag in order
        $paramsStart = reset($paramAnnotations)->getStart();
        $paramsEnd = end($paramAnnotations)->getEnd();

        foreach ($doc->getAnnotations() as $annotation) {
            if ($annotation->getStart() < $paramsStart || $annotation->getEnd() > $paramsEnd) {
                continue;
            }

            $annotation->remove();
            $doc
                ->getLine($annotation->getStart())
                ->setContent(current($orderedAnnotations))
            ;

            next($orderedAnnotations);
        }

        return $doc;
    }

    /**
     * Sort the param annotations according to the function parameters.
     *
     * @param list<Token>                $funcParamNames
     * @param non-empty-list<Annotation> $paramAnnotations
     *
     * @return non-empty-list<string>
     */
    private function sortParamAnnotations(array $funcParamNames, array $paramAnnotations): array
    {
        $validParams = [];
        foreach ($funcParamNames as $paramName) {
            foreach ($this->findParamAnnotationByIdentifier($paramAnnotations, $paramName->getContent()) as $index => $annotation) {
                // Found an exactly matching @param annotation
                $validParams[$index] = $annotation->getContent();
            }
        }

        // Detect superfluous annotations
        $invalidParams = array_values(
            array_diff_key($paramAnnotations, $validParams),
        );

        // Append invalid parameters to the (ordered) valid ones
        $orderedParams = array_values($validParams);
        foreach ($invalidParams as $params) {
            $orderedParams[] = $params->getContent();
        }
        \assert(\count($orderedParams) > 0);

        return $orderedParams;
    }

    /**
     * Fetch all annotations except the param ones.
     *
     * @param list<Annotation> $paramAnnotations
     *
     * @return list<string>
     */
    private function getOtherAnnotationsBetweenParams(DocBlock $doc, array $paramAnnotations): array
    {
        if (0 === \count($paramAnnotations)) {
            return [];
        }

        $paramsStart = reset($paramAnnotations)->getStart();
        $paramsEnd = end($paramAnnotations)->getEnd();

        $otherAnnotations = [];
        foreach ($doc->getAnnotations() as $annotation) {
            if ($annotation->getStart() < $paramsStart || $annotation->getEnd() > $paramsEnd) {
                continue;
            }

            if (self::PARAM_TAG !== $annotation->getTag()->getName()) {
                $otherAnnotations[] = $annotation->getContent();
            }
        }

        return $otherAnnotations;
    }

    /**
     * Return the indices of the lines of a specific parameter annotation.
     *
     * @param list<Annotation> $paramAnnotations
     *
     * @return array<int, Annotation> Mapping of found indices and corresponding Annotations
     */
    private function findParamAnnotationByIdentifier(array $paramAnnotations, string $identifier): array
    {
        $blockLevel = 0;
        $blockMatch = false;
        $blockIndices = [];

        $paramRegex = '/\*\h*@param\h*(?:|'.TypeExpression::REGEX_TYPES.'\h*)&?(?=\$\b)'.preg_quote($identifier).'\b/';

        foreach ($paramAnnotations as $i => $param) {
            $blockStart = Preg::match('/\s*{\s*/', $param->getContent());
            $blockEndMatches = Preg::matchAll('/}[\*\s\n]*/', $param->getContent());

            if (0 === $blockLevel && Preg::match($paramRegex, $param->getContent())) {
                if ($blockStart) {
                    $blockMatch = true; // Start of a nested block
                } else {
                    return [$i => $param]; // Top level match
                }
            }

            if ($blockStart) {
                ++$blockLevel;
            }

            if (0 !== $blockEndMatches) {
                $blockLevel -= $blockEndMatches;
            }

            if ($blockMatch) {
                $blockIndices[$i] = $param;
                if (0 === $blockLevel) {
                    return $blockIndices;
                }
            }
        }

        return [];
    }
}
