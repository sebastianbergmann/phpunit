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

namespace PhpCsFixer\Tokenizer;

/**
 * @phpstan-import-type _PhpTokenPrototype from Token
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractTypeTransformer extends AbstractTransformer
{
    private const TYPE_END_TOKENS = [')', [\T_CALLABLE], [\T_NS_SEPARATOR], [\T_STATIC], [\T_STRING], [CT::T_ARRAY_TYPEHINT]];

    private const TYPE_TOKENS = [
        '|', '&', '(',
        ...self::TYPE_END_TOKENS,
        [CT::T_TYPE_ALTERNATION], [CT::T_TYPE_INTERSECTION], // some siblings may already be transformed
        [\T_WHITESPACE], [\T_COMMENT], [\T_DOC_COMMENT], // technically these can be inside of type tokens array
    ];

    abstract protected function replaceToken(Tokens $tokens, int $index): void;

    /**
     * @param _PhpTokenPrototype $originalToken
     */
    protected function doProcess(Tokens $tokens, int $index, $originalToken): void
    {
        if (!$tokens[$index]->equals($originalToken)) {
            return;
        }

        if (!$this->isPartOfType($tokens, $index)) {
            return;
        }

        $this->replaceToken($tokens, $index);
    }

    private function isPartOfType(Tokens $tokens, int $index): bool
    {
        // return types and non-capturing catches
        $typeColonIndex = $tokens->getTokenNotOfKindSibling($index, -1, self::TYPE_TOKENS);
        if ($tokens[$typeColonIndex]->isGivenKind([\T_CATCH, CT::T_TYPE_COLON, \T_CONST])) {
            return true;
        }

        // for parameter there will be splat operator or variable after the type ("&" is ambiguous and can be reference or bitwise and)
        $afterTypeIndex = $tokens->getTokenNotOfKindSibling($index, 1, self::TYPE_TOKENS);

        if ($tokens[$afterTypeIndex]->isGivenKind(\T_ELLIPSIS)) {
            return true;
        }

        if (!$tokens[$afterTypeIndex]->isGivenKind(\T_VARIABLE)) {
            return false;
        }

        $beforeVariableIndex = $tokens->getPrevMeaningfulToken($afterTypeIndex);
        if ($tokens[$beforeVariableIndex]->equals('&')) {
            $prevIndex = $tokens->getPrevTokenOfKind(
                $index,
                [
                    '{',
                    '}',
                    ';',
                    [\T_CLOSE_TAG],
                    [\T_FN],
                    [\T_FUNCTION],
                ],
            );

            return null !== $prevIndex && $tokens[$prevIndex]->isGivenKind([\T_FN, \T_FUNCTION]);
        }

        return $tokens[$beforeVariableIndex]->equalsAny(self::TYPE_END_TOKENS);
    }
}
