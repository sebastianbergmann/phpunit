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
use PhpCsFixer\DocBlock\Line;
use PhpCsFixer\DocBlock\TypeExpression;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 * @author Dave van der Brugge <dmvdbrugge@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpdocVarWithoutNameFixer extends AbstractFixer
{
    private const PROPERTY_MODIFIER_KINDS = [\T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_VAR, FCT::T_READONLY, FCT::T_PRIVATE_SET, FCT::T_PROTECTED_SET, FCT::T_PUBLIC_SET];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            '`@var` and `@type` annotations of classy properties should not contain the name.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class Foo
                        {
                            /**
                             * @var int $bar
                             */
                            public $bar;

                            /**
                             * @type $baz float
                             */
                            public $baz;
                        }

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
        return $tokens->isTokenKindFound(\T_DOC_COMMENT) && $tokens->isAnyTokenKindsFound([\T_CLASS, \T_TRAIT]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);

            if (null === $nextIndex) {
                continue;
            }

            // For people writing "static public $foo" instead of "public static $foo"
            if ($tokens[$nextIndex]->isGivenKind(\T_STATIC)) {
                $nextIndex = $tokens->getNextMeaningfulToken($nextIndex);
            }

            // We want only doc blocks that are for properties and thus have specified access modifiers next
            if (!$tokens[$nextIndex]->isGivenKind(self::PROPERTY_MODIFIER_KINDS)) {
                continue;
            }

            $doc = new DocBlock($token->getContent());

            $firstLevelLines = $this->getFirstLevelLines($doc);
            $annotations = $doc->getAnnotationsOfType(['type', 'var']);

            foreach ($annotations as $annotation) {
                if (isset($firstLevelLines[$annotation->getStart()])) {
                    $this->fixLine($firstLevelLines[$annotation->getStart()]);
                }
            }

            $tokens[$index] = new Token([\T_DOC_COMMENT, $doc->getContent()]);
        }
    }

    private function fixLine(Line $line): void
    {
        Preg::matchAll('/ \$'.TypeExpression::REGEX_IDENTIFIER.'(?<!\$this)/', $line->getContent(), $matches);

        foreach ($matches[0] as $match) {
            $line->setContent(str_replace($match, '', $line->getContent()));
        }
    }

    /**
     * @return array<int, Line>
     */
    private function getFirstLevelLines(DocBlock $docBlock): array
    {
        $nested = 0;
        $lines = $docBlock->getLines();

        foreach ($lines as $index => $line) {
            $content = $line->getContent();

            if (Preg::match('/\s*\*\s*}$/', $content)) {
                --$nested;
            }

            if ($nested > 0) {
                unset($lines[$index]);
            }

            if (Preg::match('/\s\{$/', $content)) {
                ++$nested;
            }
        }

        return $lines;
    }
}
