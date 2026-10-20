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

namespace PhpCsFixer\Fixer\PhpUnit;

use PhpCsFixer\Fixer\AbstractPhpUnitFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpUnitFqcnAnnotationFixer extends AbstractPhpUnitFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHPUnit annotations should be a FQCNs including a root namespace.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        final class MyTest extends \PHPUnit_Framework_TestCase
                        {
                            /**
                             * @expectedException InvalidArgumentException
                             * @covers Project\NameSpace\Something
                             * @coversDefaultClass Project\Default
                             * @uses Project\Test\Util
                             */
                            public function testSomeTest()
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
     * Must run before NoUnusedImportsFixer, PhpdocOrderByValueFixer.
     */
    public function getPriority(): int
    {
        return -9;
    }

    protected function applyPhpUnitClassFix(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        $prevDocCommentIndex = $tokens->getPrevTokenOfKind($startIndex, [[\T_DOC_COMMENT]]);

        if (null !== $prevDocCommentIndex) {
            $startIndex = $prevDocCommentIndex;
        }

        $this->fixPhpUnitClass($tokens, $startIndex, $endIndex);
    }

    private function fixPhpUnitClass(Tokens $tokens, int $startIndex, int $endIndex): void
    {
        for ($index = $startIndex; $index < $endIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                $tokens[$index] = new Token([\T_DOC_COMMENT, Preg::replace(
                    '~^(\s*\*\s*@(?:expectedException|covers|coversDefaultClass|uses)\h+)(?!(?:self|static)::)(\w.*)$~m',
                    '$1\\\$2',
                    $tokens[$index]->getContent(),
                )]);
            }
        }
    }
}
