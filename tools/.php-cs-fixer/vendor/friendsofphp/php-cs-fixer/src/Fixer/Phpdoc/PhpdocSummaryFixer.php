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
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\ShortDescription;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocSummaryFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPDoc summary should end in either a full stop, exclamation mark, or question mark.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         * Foo function is great
                         */
                        function foo () {}

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            $doc = new DocBlock($token->getContent());
            $end = (new ShortDescription($doc))->getEnd();

            if (null !== $end) {
                $line = $doc->getLine($end);
                $content = rtrim($line->getContent());

                if (
                    // final line of Description is NOT properly formatted
                    !$this->isCorrectlyFormatted($content)
                    // and first line  of Description, if different than final line, does NOT indicate a list
                    && (1 === $end || ($doc->isMultiLine() && ':' !== substr(rtrim($doc->getLine(1)->getContent()), -1)))
                ) {
                    $line->setContent($content.'.'.$this->whitespacesConfig->getLineEnding());
                    $tokens[$index] = new Token([\T_DOC_COMMENT, $doc->getContent()]);
                }
            }
        }
    }

    /**
     * Is the last line of the short description correctly formatted?
     */
    private function isCorrectlyFormatted(string $content): bool
    {
        if (str_contains(strtolower($content), strtolower('{@inheritdoc}'))) {
            return true;
        }

        return $content !== rtrim($content, '.:。!?¡¿！？');
    }
}
