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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoEmptyPhpdocFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be empty PHPDoc blocks.',
            [new CodeSample("<?php /**  */\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer, PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, GeneralPhpdocAnnotationRemoveFixer, NoSuperfluousPhpdocTagsFixer, PhpUnitAttributesFixer, PhpUnitNoExpectationAnnotationFixer, PhpUnitTestAnnotationFixer, PhpdocAddMissingParamAnnotationFixer, PhpdocIndentFixer, PhpdocNoAccessFixer, PhpdocNoEmptyReturnFixer, PhpdocNoPackageFixer, PhpdocNoUselessInheritdocFixer, PhpdocReadonlyClassCommentToKeywordFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     */
    public function getPriority(): int
    {
        return 3;
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

            if (!Preg::match('#^/\*\*[\s\*]*\*/$#', $token->getContent())) {
                continue;
            }

            if (
                $tokens[$index - 1]->isGivenKind([\T_OPEN_TAG, \T_WHITESPACE])
                && substr_count($tokens[$index - 1]->getContent(), "\n") > 0
                && $tokens[$index + 1]->isGivenKind(\T_WHITESPACE)
                && Preg::match('/^\R/', $tokens[$index + 1]->getContent())
            ) {
                $tokens[$index - 1] = new Token([
                    $tokens[$index - 1]->getId(),
                    Preg::replace('/\h*$/', '', $tokens[$index - 1]->getContent()),
                ]);

                $newContent = Preg::replace('/^\R/', '', $tokens[$index + 1]->getContent());
                if ('' === $newContent) {
                    $tokens->clearAt($index + 1);
                } else {
                    $tokens[$index + 1] = new Token([\T_WHITESPACE, $newContent]);
                }
            }

            $tokens->clearTokenAndMergeSurroundingWhitespace($index);
        }
    }
}
