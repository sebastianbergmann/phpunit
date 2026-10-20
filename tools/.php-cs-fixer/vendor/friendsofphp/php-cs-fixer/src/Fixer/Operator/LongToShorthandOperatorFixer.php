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

namespace PhpCsFixer\Fixer\Operator;

use PhpCsFixer\Fixer\AbstractShortOperatorFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @phpstan-import-type _PhpTokenArray from Token
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class LongToShorthandOperatorFixer extends AbstractShortOperatorFixer
{
    /**
     * @var non-empty-array<string, _PhpTokenArray>
     */
    private const OPERATORS = [
        '+' => [\T_PLUS_EQUAL, '+='],
        '-' => [\T_MINUS_EQUAL, '-='],
        '*' => [\T_MUL_EQUAL, '*='],
        '/' => [\T_DIV_EQUAL, '/='],
        '&' => [\T_AND_EQUAL, '&='],
        '.' => [\T_CONCAT_EQUAL, '.='],
        '%' => [\T_MOD_EQUAL, '%='],
        '|' => [\T_OR_EQUAL, '|='],
        '^' => [\T_XOR_EQUAL, '^='],
    ];

    /**
     * @var non-empty-list<string>
     */
    private array $operatorTypes;

    private TokensAnalyzer $tokensAnalyzer;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Shorthand notation for operators should be used if possible.',
            [
                new CodeSample("<?php\n\$i = \$i + 10;\n"),
            ],
            null,
            'Risky when applying for string offsets (e.g. `<?php $text = "foo"; $text[0] = $text[0] & "\x7F";`).',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before BinaryOperatorSpacesFixer, NoExtraBlankLinesFixer, NoSinglelineWhitespaceBeforeSemicolonsFixer, StandardizeIncrementFixer.
     */
    public function getPriority(): int
    {
        return 17;
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([...array_keys(self::OPERATORS), FCT::T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG, FCT::T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG]);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->operatorTypes = array_keys(self::OPERATORS);
        $this->tokensAnalyzer = new TokensAnalyzer($tokens);

        parent::applyFix($file, $tokens);
    }

    protected function isOperatorTokenCandidate(Tokens $tokens, int $index): bool
    {
        if (!$tokens[$index]->equalsAny($this->operatorTypes)) {
            return false;
        }

        while (null !== $index) {
            $index = $tokens->getNextMeaningfulToken($index);
            $otherToken = $tokens[$index];

            if ($otherToken->equalsAny([';', [\T_CLOSE_TAG]])) {
                return true;
            }

            // fast precedence check
            if ($otherToken->equals('?') || $otherToken->isGivenKind(\T_INSTANCEOF)) {
                return false;
            }

            $blockType = Tokens::detectBlockType($otherToken);

            if (null !== $blockType) {
                if (false === $blockType['isStart']) {
                    return true;
                }

                $index = $tokens->findBlockEnd($blockType['type'], $index);

                continue;
            }

            // precedence check
            if ($this->tokensAnalyzer->isBinaryOperator($index)) {
                return false;
            }
        }

        return false; // unreachable, but keeps SCA happy
    }

    protected function getReplacementToken(Token $token): Token
    {
        \assert(isset(self::OPERATORS[$token->getContent()])); // for PHPStan

        return new Token(self::OPERATORS[$token->getContent()]);
    }
}
