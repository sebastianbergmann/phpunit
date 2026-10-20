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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocNoDuplicateTypesFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Removes duplicate PHPDoc types.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        /**
                         * @param string|string|int $bar
                         */

                        PHP,
                ),
            ],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocArrayTypeFixer, PhpdocIndentFixer, PhpdocListTypeFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
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
            $annotations = $doc->getAnnotationsOfType(Annotation::TAGS_WITH_TYPES);

            if (0 === \count($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                // fix main types
                if (null !== $annotation->getTypeExpression()) {
                    $annotation->setTypes(
                        $annotation->getTypeExpression()
                            ->removeDuplicateTypes()
                            ->getTypes(),
                    );
                }

                // fix @method parameters types
                $line = $doc->getLine($annotation->getStart());
                $line->setContent(Preg::replaceCallback('/\*\h*@method\h+'.TypeExpression::REGEX_TYPES.'\h+\K(?&callable)/', static function (array $matches): string {
                    \assert(isset($matches[0]));

                    $typeExpression = new TypeExpression($matches[0], null, []);

                    return implode('|', $typeExpression->removeDuplicateTypes()->getTypes());
                }, $line->getContent()));
            }

            $tokens[$index] = new Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }
}
