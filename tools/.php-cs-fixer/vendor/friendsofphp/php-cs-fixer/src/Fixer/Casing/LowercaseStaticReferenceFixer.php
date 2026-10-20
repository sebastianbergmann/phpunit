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

namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Kuba Werłos <werlos@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class LowercaseStaticReferenceFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Class static references `self`, `static` and `parent` MUST be in lower case.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Foo extends Bar
                        {
                            public function baz1()
                            {
                                return STATIC::baz2();
                            }

                            public function baz2($x)
                            {
                                return $x instanceof Self;
                            }

                            public function baz3(PaRent $x)
                            {
                                return true;
                            }
                        }

                        PHP,
                ),
                new CodeSample(
                    <<<'PHP'
                        <?php
                        class Foo extends Bar
                        {
                            public function baz(?self $x) : SELF
                            {
                                return false;
                            }
                        }

                        PHP,
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_STATIC, \T_STRING]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->equalsAny([[\T_STRING, 'self'], [\T_STATIC, 'static'], [\T_STRING, 'parent']], false)) {
                continue;
            }

            if (!self::isTokenToFix($tokens, $index)) {
                continue;
            }

            $tokens[$index] = new Token([$token->getId(), strtolower($token->getContent())]);
        }
    }

    private static function isTokenToFix(Tokens $tokens, int $index): bool
    {
        if ($tokens[$index]->getContent() === strtolower($tokens[$index]->getContent())) {
            return false; // case is already correct
        }

        $nextIndex = $tokens->getNextMeaningfulToken($index);
        if ($tokens[$nextIndex]->isGivenKind(\T_DOUBLE_COLON)) {
            return true;
        }
        if (!$tokens[$nextIndex]->isGivenKind([\T_VARIABLE, CT::T_TYPE_ALTERNATION]) && !$tokens[$nextIndex]->equalsAny(['(', ')', '{', ';'])) {
            return false;
        }

        $prevIndex = $tokens->getPrevMeaningfulToken($index);
        if ($tokens[$prevIndex]->isGivenKind(\T_INSTANCEOF)) {
            return true;
        }
        if ($tokens[$prevIndex]->isGivenKind(\T_CASE)) {
            return !$tokens[$nextIndex]->equals(';');
        }
        if (!$tokens[$prevIndex]->isGivenKind([\T_NEW, \T_PRIVATE, \T_PROTECTED, \T_PUBLIC, CT::T_NULLABLE_TYPE, CT::T_TYPE_COLON, CT::T_TYPE_ALTERNATION]) && !$tokens[$prevIndex]->equalsAny(['(', '{'])) {
            return false;
        }

        if ($tokens[$prevIndex]->equals('(') && $tokens[$nextIndex]->equals(')')) {
            return false;
        }

        if ('static' === strtolower($tokens[$index]->getContent()) && $tokens[$nextIndex]->isGivenKind(\T_VARIABLE)) {
            return false;
        }

        return true;
    }
}
