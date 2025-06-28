<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\Analyzer\ConstructorAnalyzer;

/**
 * @no-named-arguments
 */
final class ReadonlyPromotedPropertiesFixer extends AbstractFixer
{
    /** @var list<int> */
    private array $promotedPropertyVisibilityKinds;

    public function __construct()
    {
        $this->promotedPropertyVisibilityKinds = [
            CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE,
            CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED,
            CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC,
        ];
        if (\defined('T_PUBLIC_SET')) {
            $this->promotedPropertyVisibilityKinds[] = \T_PUBLIC_SET;
            $this->promotedPropertyVisibilityKinds[] = \T_PROTECTED_SET;
            $this->promotedPropertyVisibilityKinds[] = \T_PRIVATE_SET;
        }
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Promoted properties must be declared as read-only.',
            [
                new VersionSpecificCodeSample(
                    '<?php class Foo {
    public function __construct(
        public array $a,
        public bool $b,
    ) {}
}
',
                    new VersionSpecification(80100),
                ),
            ],
            '',
            'when property is written',
        );
    }

    /**
     * Must run after PromotedConstructorPropertyFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return \defined('T_READONLY') && $tokens->isAnyTokenKindsFound($this->promotedPropertyVisibilityKinds);
    }

    public function isRisky(): bool
    {
        return true;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $constructorAnalyzer = new ConstructorAnalyzer();

        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            if ($this->isClassReadonly($tokens, $index)) {
                continue;
            }

            $constructorAnalysis = $constructorAnalyzer->findNonAbstractConstructor($tokens, $index);
            if ($constructorAnalysis === null) {
                continue;
            }

            $constructorNameIndex = $tokens->getNextMeaningfulToken($constructorAnalysis->getConstructorIndex());

            $classOpenBraceIndex = $tokens->getNextTokenOfKind($index, ['{']);
            \assert(\is_int($classOpenBraceIndex));
            $classCloseBraceIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpenBraceIndex);

            $constructorOpenParenthesisIndex = $tokens->getNextTokenOfKind($constructorAnalysis->getConstructorIndex(), ['(']);
            \assert(\is_int($constructorOpenParenthesisIndex));
            $constructorCloseParenthesisIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $constructorOpenParenthesisIndex);

            $this->fixParameters(
                $tokens,
                $classOpenBraceIndex,
                $classCloseBraceIndex,
                $constructorOpenParenthesisIndex,
                $constructorCloseParenthesisIndex,
            );
        }
    }

    private function isClassReadonly(Tokens $tokens, int $index): bool
    {
        do {
            $index = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($index));
        } while ($tokens[$index]->isGivenKind([\T_ABSTRACT, \T_FINAL]));

        return $tokens[$index]->isGivenKind(\T_READONLY);
    }

    private function fixParameters(
        Tokens $tokens,
        int $classOpenBraceIndex,
        int $classCloseBraceIndex,
        int $constructorOpenParenthesisIndex,
        int $constructorCloseParenthesisIndex
    ): void {
        for ($index = $constructorCloseParenthesisIndex; $index > $constructorOpenParenthesisIndex; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_VARIABLE)) {
                continue;
            }

            $insertIndex = $this->getInsertIndex($tokens, $index);
            if ($insertIndex === null) {
                continue;
            }

            $propertyAssignment = $tokens->findSequence(
                [
                    [\T_VARIABLE, '$this'],
                    [\T_OBJECT_OPERATOR],
                    [\T_STRING, \substr($tokens[$index]->getContent(), 1)],
                ],
                $classOpenBraceIndex,
                $classCloseBraceIndex,
            );
            if ($propertyAssignment !== null) {
                continue;
            }

            $tokens->insertAt(
                $insertIndex + 1,
                [
                    new Token([\T_WHITESPACE, ' ']),
                    new Token([\T_READONLY, 'readonly']),
                ],
            );
        }
    }

    private function getInsertIndex(Tokens $tokens, int $index): ?int
    {
        $insertIndex = null;

        $index = $tokens->getPrevMeaningfulToken($index);
        \assert(\is_int($index));
        while (!$tokens[$index]->equalsAny([',', '('])) {
            $index = $tokens->getPrevMeaningfulToken($index);
            \assert(\is_int($index));
            if ($tokens[$index]->isGivenKind(\T_READONLY)) {
                return null;
            }
            if ($insertIndex === null && $tokens[$index]->isGivenKind($this->promotedPropertyVisibilityKinds)) {
                $insertIndex = $index;
            }
        }

        return $insertIndex;
    }
}
