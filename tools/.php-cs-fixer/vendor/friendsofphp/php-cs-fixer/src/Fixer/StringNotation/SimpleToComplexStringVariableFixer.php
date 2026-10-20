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

namespace PhpCsFixer\Fixer\StringNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dave van der Brugge <dmvdbrugge@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SimpleToComplexStringVariableFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts explicit variables in double-quoted strings and heredoc syntax from simple to complex format (`${` to `{$`).',
            [
                new CodeSample(
                    <<<'EOT'
                        <?php
                        $name = 'World';
                        echo "Hello ${name}!";

                        EOT,
                ),
                new CodeSample(
                    <<<'EOT'
                        <?php
                        $name = 'World';
                        echo <<<TEST
                        Hello ${name}!
                        TEST;

                        EOT,
                ),
            ],
            "Doesn't touch implicit variables. Works together nicely with `explicit_string_variable`.",
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after ExplicitStringVariableFixer.
     */
    public function getPriority(): int
    {
        return -10;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOLLAR_OPEN_CURLY_BRACES);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = \count($tokens) - 3; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_DOLLAR_OPEN_CURLY_BRACES)) {
                continue;
            }
            $varnameToken = $tokens[$index + 1];

            if (!$varnameToken->isGivenKind(\T_STRING_VARNAME)) {
                continue;
            }

            $dollarCloseToken = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_COMPLEX_STRING_VARIABLE, $index);

            $prevTokenContent = $tokens[$index - 1]->getContent();
            if (str_ends_with($prevTokenContent, '$') && !str_ends_with($prevTokenContent, '\$')) {
                $tokens[$index - 1] = new Token([\T_ENCAPSED_AND_WHITESPACE, substr($prevTokenContent, 0, -1).'\$']);
            }
            $tokens[$index] = new Token([\T_CURLY_OPEN, '{']);
            $tokens[$index + 1] = new Token([\T_VARIABLE, '$'.$varnameToken->getContent()]);
            $tokens[$dollarCloseToken] = new Token([CT::T_CURLY_CLOSE, '}']);
        }
    }
}
