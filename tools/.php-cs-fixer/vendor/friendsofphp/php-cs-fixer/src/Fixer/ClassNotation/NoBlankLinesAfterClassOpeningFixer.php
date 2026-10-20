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

namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Ceeram <ceeram@cakephp.org>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoBlankLinesAfterClassOpeningFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should be no empty lines after class opening brace.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Sample
                        {

                            protected function foo()
                            {
                            }
                        }

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after OrderedClassElementsFixer, PhpUnitDataProviderMethodOrderFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isClassy()) {
                continue;
            }

            $startBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);
            if (!$tokens[$startBraceIndex + 1]->isWhitespace()) {
                continue;
            }

            $this->fixWhitespace($tokens, $startBraceIndex + 1);
        }
    }

    /**
     * Cleanup a whitespace token.
     */
    private function fixWhitespace(Tokens $tokens, int $index): void
    {
        $content = $tokens[$index]->getContent();
        // if there is more than one new line in the whitespace, then we need to fix it
        if (substr_count($content, "\n") > 1) {
            // the final bit of the whitespace must be the next statement's indentation
            $tokens[$index] = new Token([\T_WHITESPACE, $this->whitespacesConfig->getLineEnding().substr($content, (int) strrpos($content, "\n") + 1)]);
        }
    }
}
