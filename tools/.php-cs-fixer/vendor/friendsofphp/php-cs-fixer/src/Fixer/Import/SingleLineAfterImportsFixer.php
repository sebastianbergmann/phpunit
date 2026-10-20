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

namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixer\Utils;

/**
 * Fixer for rules defined in PSR2 ¶3.
 *
 * @author Ceeram <ceeram@cakephp.org>
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SingleLineAfterImportsFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_USE);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Each namespace use MUST go on its own line and there MUST be one blank line after the use statements block.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        namespace Foo;

                        use Bar;
                        use Baz;
                        final class Example
                        {
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        namespace Foo;

                        use Bar;
                        use Baz;


                        final class Example
                        {
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after NoUnusedImportsFixer.
     */
    public function getPriority(): int
    {
        return -11;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $ending = $this->whitespacesConfig->getLineEnding();
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        $added = 0;
        foreach ($tokensAnalyzer->getImportUseIndexes() as $index) {
            $index += $added;
            $indent = '';

            // if previous line ends with comment and current line starts with whitespace, use current indent
            if ($tokens[$index - 1]->isWhitespace(" \t") && $tokens[$index - 2]->isGivenKind(\T_COMMENT)) {
                $indent = $tokens[$index - 1]->getContent();
            } elseif ($tokens[$index - 1]->isWhitespace()) {
                $indent = Utils::calculateTrailingWhitespaceIndent($tokens[$index - 1]);
            }

            $semicolonIndex = $tokens->getNextTokenOfKind($index, [';', [\T_CLOSE_TAG]]); // Handle insert index for inline T_COMMENT with whitespace after semicolon
            $insertIndex = $semicolonIndex;

            if ($tokens[$semicolonIndex]->isGivenKind(\T_CLOSE_TAG)) {
                if ($tokens[$insertIndex - 1]->isWhitespace()) {
                    --$insertIndex;
                }

                $tokens->insertAt($insertIndex, new Token(';'));
                ++$added;
            }

            if ($semicolonIndex === \count($tokens) - 1) {
                $tokens->insertAt($insertIndex + 1, new Token([\T_WHITESPACE, $ending.$ending.$indent]));
                ++$added;
            } else {
                $newline = $ending;
                $tokens[$semicolonIndex]->isGivenKind(\T_CLOSE_TAG) ? --$insertIndex : ++$insertIndex;
                if ($tokens[$insertIndex]->isWhitespace(" \t") && $tokens[$insertIndex + 1]->isComment()) {
                    ++$insertIndex;
                }

                // Increment insert index for inline T_COMMENT or T_DOC_COMMENT
                if ($tokens[$insertIndex]->isComment()) {
                    ++$insertIndex;
                }

                $afterSemicolon = $tokens->getNextMeaningfulToken($semicolonIndex);
                if (null === $afterSemicolon || !$tokens[$afterSemicolon]->isGivenKind(\T_USE)) {
                    $newline .= $ending;
                }

                if ($tokens[$insertIndex]->isWhitespace()) {
                    $nextToken = $tokens[$insertIndex];
                    if (2 === substr_count($nextToken->getContent(), "\n")) {
                        continue;
                    }
                    $nextMeaningfulAfterUseIndex = $tokens->getNextMeaningfulToken($insertIndex);
                    if (null !== $nextMeaningfulAfterUseIndex && $tokens[$nextMeaningfulAfterUseIndex]->isGivenKind(\T_USE)) {
                        if (substr_count($nextToken->getContent(), "\n") < 1) {
                            $tokens[$insertIndex] = new Token([\T_WHITESPACE, $newline.$indent.ltrim($nextToken->getContent())]);
                        }
                    } else {
                        $tokens[$insertIndex] = new Token([\T_WHITESPACE, $newline.$indent.ltrim($nextToken->getContent())]);
                    }
                } else {
                    $tokens->insertAt($insertIndex, new Token([\T_WHITESPACE, $newline.$indent]));
                    ++$added;
                }
            }
        }
    }
}
