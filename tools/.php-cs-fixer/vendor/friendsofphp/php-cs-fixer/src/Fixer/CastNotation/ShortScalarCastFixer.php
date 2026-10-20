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

namespace PhpCsFixer\Fixer\CastNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ShortScalarCastFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Cast `(boolean)` and `(integer)` should be written as `(bool)` and `(int)`, `(double)` and `(real)` as `(float)`, `(binary)` as `(string)`.',
            [
                new CodeSample(
                    "<?php\n\$a = (boolean) \$b;\n\$a = (integer) \$b;\n\$a = (double) \$b;\n\n\$a = (binary) \$b;\n",
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getCastTokenKinds());
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            if (!$tokens[$index]->isCast()) {
                continue;
            }

            $castFrom = trim(substr($tokens[$index]->getContent(), 1, -1));
            $castFromLowered = strtolower($castFrom);

            $castTo = [
                'boolean' => 'bool',
                'integer' => 'int',
                'double' => 'float',
                'real' => 'float',
                'binary' => 'string',
            ][$castFromLowered] ?? null;

            if (null === $castTo) {
                continue;
            }

            $tokens[$index] = new Token([
                $tokens[$index]->getId(),
                str_replace($castFrom, $castTo, $tokens[$index]->getContent()),
            ]);
        }
    }
}
