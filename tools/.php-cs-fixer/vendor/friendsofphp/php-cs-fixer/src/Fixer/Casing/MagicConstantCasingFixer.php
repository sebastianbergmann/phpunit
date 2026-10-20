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

namespace PhpCsFixer\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author ntzm
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class MagicConstantCasingFixer extends AbstractFixer
{
    private const MAGIC_CONSTANTS = [
        \T_LINE => '__LINE__',
        \T_FILE => '__FILE__',
        \T_DIR => '__DIR__',
        \T_FUNC_C => '__FUNCTION__',
        \T_CLASS_C => '__CLASS__',
        \T_METHOD_C => '__METHOD__',
        \T_NS_C => '__NAMESPACE__',
        CT::T_CLASS_CONSTANT => 'class',
        \T_TRAIT_C => '__TRAIT__',
        FCT::T_PROPERTY_C => '__PROPERTY__',
    ];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Magic constants should be referred to using the correct casing.',
            [new CodeSample("<?php\necho __dir__;\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        static $magicConstantTokenIds = null;

        if (null === $magicConstantTokenIds) {
            $magicConstantTokenIds = array_keys(self::MAGIC_CONSTANTS);
        }

        return $tokens->isAnyTokenKindsFound($magicConstantTokenIds);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            $tokenId = $token->getId();

            if (null === $tokenId || !isset(self::MAGIC_CONSTANTS[$tokenId])) {
                continue;
            }

            $tokens[$index] = new Token([$tokenId, self::MAGIC_CONSTANTS[$tokenId]]);
        }
    }
}
