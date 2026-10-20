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

namespace PhpCsFixer\Fixer\AttributeNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\IndentationTrait;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Albin Kester <albin.kester@gmail.com>
 *
 * @see https://www.php-fig.org/per/coding-style/#121-basics
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AttributeBlockNoSpacesFixer extends AbstractFixer
{
    use IndentationTrait;

    public function getDefinition(): FixerDefinition
    {
        return new FixerDefinition(
            'Remove spaces before and after the attributes block.',
            [
                new CodeSample(
                    '<?php
class User
{
    #[
        ApiProperty(identifier: true)
    ]
    private string $name;
}
',
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(FCT::T_ATTRIBUTE);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $index = 0;

        while (null !== $index = $tokens->getNextTokenOfKind($index, [[\T_ATTRIBUTE]])) {
            $toDelete = [];

            $attributeAnalysis = AttributeAnalyzer::collectOne($tokens, $index);

            $index = $attributeAnalysis->getOpeningBracketIndex();
            $token = $tokens[$index];
            \assert($token->isGivenKind([\T_ATTRIBUTE]));

            $nextTokenIndex = $tokens->getNextMeaningfulToken($index);
            for ($i = $index + 1; $i < $nextTokenIndex; ++$i) {
                if (!$tokens[$i]->isWhitespace()) {
                    $toDelete = [];

                    break;
                }
                $toDelete[] = $i;
            }
            unset($i);

            $index = $attributeAnalysis->getClosingBracketIndex();
            $token = $tokens[$index];
            \assert($token->isGivenKind([CT::T_ATTRIBUTE_CLOSE]));

            $prevTokenIndex = $tokens->getPrevMeaningfulToken($index);
            for ($i = $prevTokenIndex + 1; $i < $index; ++$i) {
                if (!$tokens[$i]->isWhitespace()) {
                    $toDelete = [];

                    break;
                }
                $toDelete[] = $i;
            }
            unset($i);

            foreach ($toDelete as $i) {
                $tokens->clearAt($i);
            }
        }
    }
}
