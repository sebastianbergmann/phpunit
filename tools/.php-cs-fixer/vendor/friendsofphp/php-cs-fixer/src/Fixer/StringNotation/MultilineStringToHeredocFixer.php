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
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Michael Vorisek <https://github.com/mvorisek>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class MultilineStringToHeredocFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Convert multiline string to `heredoc` or `nowdoc`.',
            [
                new CodeSample(
                    <<<'EOD'
                        <?php
                        $a = 'line1
                        line2';
                        EOD."\n",
                ),
                new CodeSample(
                    <<<'EOD'
                        <?php
                        $a = "line1
                        {$obj->getName()}";
                        EOD."\n",
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_CONSTANT_ENCAPSED_STRING, \T_ENCAPSED_AND_WHITESPACE]);
    }

    /**
     * {@inheritdoc}
     *
     * Must run before EscapeImplicitBackslashesFixer, HeredocIndentationFixer, StringImplicitBackslashesFixer.
     */
    public function getPriority(): int
    {
        return 16;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $complexStringStartIndex = null;
        foreach ($tokens as $index => $token) {
            if (null === $complexStringStartIndex) {
                if ($token->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                    $this->convertStringToHeredoc($tokens, $index, $index);
                } elseif ($token->equalsAny(['"', 'b"', 'B"'])) {
                    $complexStringStartIndex = $index;
                }
            } elseif ($token->equals('"')) {
                $this->convertStringToHeredoc($tokens, $complexStringStartIndex, $index);

                $complexStringStartIndex = null;
            }
        }
    }

    private function convertStringToHeredoc(Tokens $tokens, int $stringStartIndex, int $stringEndIndex): void
    {
        $closingMarker = 'EOD';

        if ($tokens[$stringStartIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
            $content = $tokens[$stringStartIndex]->getContent();
            if ('b' === strtolower(substr($content, 0, 1))) {
                $content = substr($content, 1);
            }
            $isSingleQuoted = str_starts_with($content, '\'');
            $content = substr($content, 1, -1);

            if ($isSingleQuoted) {
                $content = Preg::replace('~\\\([\\\\\'])~', '$1', $content);
            } else {
                $content = Preg::replace('~(\\\\\\\)|\\\(")~', '$1$2', $content);
            }

            $constantStringToken = new Token([\T_ENCAPSED_AND_WHITESPACE, $content."\n"]);
        } else {
            $content = $tokens->generatePartialCode($stringStartIndex + 1, $stringEndIndex - 1);
            $isSingleQuoted = false;
            $constantStringToken = null;
        }

        if (!str_contains($content, "\n") && !str_contains($content, "\r")) {
            return;
        }

        while (Preg::match('~(^|[\r\n])\s*'.preg_quote($closingMarker, '~').'(?!\w)~', $content)) {
            $closingMarker .= '_';
        }

        $quoting = $isSingleQuoted ? '\'' : '';
        $heredocStartToken = new Token([\T_START_HEREDOC, '<<<'.$quoting.$closingMarker.$quoting."\n"]);
        $heredocEndToken = new Token([\T_END_HEREDOC, $closingMarker]);

        if (null !== $constantStringToken) {
            $tokens->overrideRange($stringStartIndex, $stringEndIndex, [
                $heredocStartToken,
                $constantStringToken,
                $heredocEndToken,
            ]);
        } else {
            for ($i = $stringStartIndex + 1; $i < $stringEndIndex; ++$i) {
                if ($tokens[$i]->isGivenKind(\T_ENCAPSED_AND_WHITESPACE)) {
                    $tokens[$i] = new Token([
                        $tokens[$i]->getId(),
                        Preg::replace('~(\\\\\\\)|\\\(")~', '$1$2', $tokens[$i]->getContent()),
                    ]);
                }
            }

            $tokens[$stringStartIndex] = $heredocStartToken;
            $tokens[$stringEndIndex] = $heredocEndToken;
            if ($tokens[$stringEndIndex - 1]->isGivenKind(\T_ENCAPSED_AND_WHITESPACE)) {
                $tokens[$stringEndIndex - 1] = new Token([
                    $tokens[$stringEndIndex - 1]->getId(),
                    $tokens[$stringEndIndex - 1]->getContent()."\n",
                ]);
            } else {
                $tokens->insertAt($stringEndIndex, new Token([
                    \T_ENCAPSED_AND_WHITESPACE,
                    "\n",
                ]));
            }
        }
    }
}
