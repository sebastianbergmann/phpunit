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

namespace PhpCsFixer;

use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\DocBlock\TypeExpression;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * This abstract fixer provides a base for fixers to fix types in PHPDoc.
 *
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractPhpdocTypesFixer extends AbstractFixer
{
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
                $this->fixType($annotation);
            }

            $tokens[$index] = new Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }

    /**
     * Actually normalize the given type.
     */
    abstract protected function normalize(string $type): string;

    /**
     * Fix the type at the given line.
     *
     * We must be super careful not to modify parts of words.
     *
     * This will be nicely handled behind the scenes for us by the annotation class.
     */
    private function fixType(Annotation $annotation): void
    {
        $typeExpression = $annotation->getTypeExpression();

        if (null === $typeExpression) {
            return;
        }

        $newTypeExpression = $typeExpression->mapTypes(function (TypeExpression $type) {
            if (!$type->isCompositeType()) {
                $value = $this->normalize($type->toString());

                return new TypeExpression($value, null, []);
            }

            return $type;
        });

        $annotation->setTypes([$newTypeExpression->toString()]);
    }
}
