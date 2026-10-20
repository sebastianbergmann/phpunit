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

namespace PhpCsFixer\Fixer\Whitespace;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\IndentationTrait;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ArrayIndentationFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    use IndentationTrait;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Each element of an array must be indented exactly once.',
            [
                new CodeSample("<?php\n\$foo = [\n   'bar' => [\n    'baz' => true,\n  ],\n];\n"),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_ARRAY, \T_LIST, CT::T_ARRAY_BRACKET_OPEN, CT::T_DESTRUCTURING_BRACKET_OPEN]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before AlignMultilineCommentFixer, BinaryOperatorSpacesFixer.
     * Must run after MethodArgumentSpaceFixer.
     */
    public function getPriority(): int
    {
        return 29;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $lastIndent = '';

        /** @var list<array{type: 'array', end_index: int, initial_indent: string}|array{type: 'expression', end_index: int, initial_indent: string, new_indent: string}> $scopes */
        $scopes = [];
        $previousLineInitialIndent = '';
        $previousLineNewIndent = '';

        foreach ($tokens as $index => $token) {
            $currentScope = [] !== $scopes ? \count($scopes) - 1 : null;

            if ($token->isComment()) {
                continue;
            }

            if (
                $token->isGivenKind([CT::T_ARRAY_BRACKET_OPEN, CT::T_DESTRUCTURING_BRACKET_OPEN])
                || ($token->equals('(') && $tokens[$tokens->getPrevMeaningfulToken($index)]->isGivenKind([\T_ARRAY, \T_LIST]))
            ) {
                $blockType = Tokens::detectBlockType($token);
                $endIndex = $tokens->findBlockEnd($blockType['type'], $index);

                $scopes[] = [
                    'type' => 'array',
                    'end_index' => $endIndex,
                    'initial_indent' => $lastIndent,
                ];

                continue;
            }

            if ($this->isNewLineToken($tokens, $index)) {
                $lastIndent = $this->extractIndent($this->computeNewLineContent($tokens, $index));
            }

            if (null === $currentScope) {
                continue;
            }

            \assert(isset($scopes[$currentScope]));

            if ($token->isWhitespace()) {
                if (!Preg::match('/\R/', $token->getContent())) {
                    continue;
                }

                if ('array' === $scopes[$currentScope]['type']) {
                    $indent = false;

                    for ($searchEndIndex = $index + 1; $searchEndIndex < $scopes[$currentScope]['end_index']; ++$searchEndIndex) {
                        $searchEndToken = $tokens[$searchEndIndex];

                        if (
                            (!$searchEndToken->isWhitespace() && !$searchEndToken->isComment())
                            || ($searchEndToken->isWhitespace() && Preg::match('/\R/', $searchEndToken->getContent()))
                        ) {
                            $indent = true;

                            break;
                        }
                    }

                    $content = Preg::replace(
                        '/(\R+)\h*$/',
                        '$1'.$scopes[$currentScope]['initial_indent'].($indent ? $this->whitespacesConfig->getIndent() : ''),
                        $token->getContent(),
                    );

                    $previousLineInitialIndent = $this->extractIndent($token->getContent());
                    $previousLineNewIndent = $this->extractIndent($content);
                } else {
                    $content = Preg::replace(
                        '/(\R)'.preg_quote($scopes[$currentScope]['initial_indent'], '/').'(\h*)$/',
                        '$1'.$scopes[$currentScope]['new_indent'].'$2',
                        $token->getContent(),
                    );
                }

                $tokens[$index] = new Token([\T_WHITESPACE, $content]);
                $lastIndent = $this->extractIndent($content);

                continue;
            }

            if ($index === $scopes[$currentScope]['end_index']) {
                while ([] !== $scopes && $index === $scopes[$currentScope]['end_index']) {
                    array_pop($scopes);
                    --$currentScope;
                }

                continue;
            }

            if ($token->equals(',')) {
                continue;
            }

            if ('expression' !== $scopes[$currentScope]['type']) {
                $endIndex = $this->findExpressionEndIndex($tokens, $index, $scopes[$currentScope]['end_index']);

                if ($endIndex === $index) {
                    continue;
                }

                $scopes[] = [
                    'type' => 'expression',
                    'end_index' => $endIndex,
                    'initial_indent' => $previousLineInitialIndent,
                    'new_indent' => $previousLineNewIndent,
                ];
            }
        }
    }

    private function findExpressionEndIndex(Tokens $tokens, int $index, int $parentScopeEndIndex): int
    {
        $endIndex = null;

        for ($searchEndIndex = $index + 1; $searchEndIndex < $parentScopeEndIndex; ++$searchEndIndex) {
            $searchEndToken = $tokens[$searchEndIndex];

            if ($searchEndToken->equalsAny(['(', '{'])
                || $searchEndToken->isGivenKind([CT::T_ARRAY_BRACKET_OPEN, CT::T_DESTRUCTURING_BRACKET_OPEN])
            ) {
                $type = Tokens::detectBlockType($searchEndToken);
                $searchEndIndex = $tokens->findBlockEnd(
                    $type['type'],
                    $searchEndIndex,
                );

                continue;
            }

            if ($searchEndToken->equals(',')) {
                $endIndex = $tokens->getPrevMeaningfulToken($searchEndIndex);

                break;
            }
        }

        return $endIndex ?? $tokens->getPrevMeaningfulToken($parentScopeEndIndex);
    }
}
