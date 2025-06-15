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

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
use PhpCsFixerCustomFixers\Analyzer\Analysis\ConstructorAnalysis;
use PhpCsFixerCustomFixers\Analyzer\ConstructorAnalyzer;
use PhpCsFixerCustomFixers\TokenRemover;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{promote_only_existing_properties?: bool}
 * @phpstan-type _Config array{promote_only_existing_properties: bool}
 *
 * @no-named-arguments
 */
final class PromotedConstructorPropertyFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /** @var array<int, array<int, Token>> */
    private array $tokensToInsert;

    private bool $promoteOnlyExistingProperties = false;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Constructor properties must be promoted if possible.',
            [
                new VersionSpecificCodeSample(
                    '<?php
class Foo {
    private string $bar;
    public function __construct(string $bar) {
        $this->bar = $bar;
    }
}
',
                    new VersionSpecification(80000),
                ),
            ],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('promote_only_existing_properties', 'whether to promote only properties that are defined in class'))
                ->setAllowedTypes(['bool'])
                ->setDefault($this->promoteOnlyExistingProperties)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('promote_only_existing_properties', $configuration)) {
            $this->promoteOnlyExistingProperties = $configuration['promote_only_existing_properties'];
        }
    }

    /**
     * Must run before ClassAttributesSeparationFixer, ConstructorEmptyBracesFixer, MultilinePromotedPropertiesFixer, NoExtraBlankLinesFixer, ReadonlyPromotedPropertiesFixer.
     */
    public function getPriority(): int
    {
        return 56;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        // @phpstan-ignore greaterOrEqual.alwaysTrue
        return \PHP_VERSION_ID >= 80000 && $tokens->isAllTokenKindsFound([\T_CLASS, \T_VARIABLE]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $constructorAnalyzer = new ConstructorAnalyzer();
        $this->tokensToInsert = [];

        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_CLASS)) {
                continue;
            }

            $constructorAnalysis = $constructorAnalyzer->findNonAbstractConstructor($tokens, $index);
            if ($constructorAnalysis === null) {
                continue;
            }

            $this->promoteProperties($tokens, $index, $constructorAnalysis);
        }

        \krsort($this->tokensToInsert);

        /**
         * @var int         $index
         * @var list<Token> $tokensToInsert
         */
        foreach ($this->tokensToInsert as $index => $tokensToInsert) {
            $tokens->insertAt($index, $tokensToInsert);
        }
    }

    private function promoteProperties(Tokens $tokens, int $classIndex, ConstructorAnalysis $constructorAnalysis): void
    {
        $isDoctrineEntity = $this->isDoctrineEntity($tokens, $classIndex);
        $properties = $this->getClassProperties($tokens, $classIndex);

        $constructorParameterNames = $constructorAnalysis->getConstructorParameterNames();
        $constructorPromotableParameters = $constructorAnalysis->getConstructorPromotableParameters();
        $constructorPromotableAssignments = $constructorAnalysis->getConstructorPromotableAssignments();

        foreach ($constructorPromotableParameters as $constructorParameterIndex => $constructorParameterName) {
            if (!\array_key_exists($constructorParameterName, $constructorPromotableAssignments)) {
                continue;
            }

            $propertyIndex = $this->getPropertyIndex($tokens, $properties, $constructorPromotableAssignments[$constructorParameterName]);

            if (!$this->isPropertyToPromote($tokens, $propertyIndex, $isDoctrineEntity)) {
                continue;
            }

            $propertyType = $this->getType($tokens, $propertyIndex);
            $parameterType = $this->getType($tokens, $constructorParameterIndex);

            if (!$this->typesAllowPromoting($propertyType, $parameterType)) {
                continue;
            }

            $assignedPropertyIndex = $tokens->getPrevTokenOfKind($constructorPromotableAssignments[$constructorParameterName], [[\T_STRING]]);
            $oldParameterName = $tokens[$constructorParameterIndex]->getContent();
            $newParameterName = '$' . $tokens[$assignedPropertyIndex]->getContent();
            if ($oldParameterName !== $newParameterName && \in_array($newParameterName, $constructorParameterNames, true)) {
                continue;
            }

            $tokensToInsert = $this->removePropertyAndReturnTokensToInsert($tokens, $propertyIndex);

            $this->renameVariable($tokens, $constructorAnalysis->getConstructorIndex(), $oldParameterName, $newParameterName);

            $this->removeAssignment($tokens, $constructorPromotableAssignments[$constructorParameterName]);
            $this->updateParameterSignature(
                $tokens,
                $constructorParameterIndex,
                $tokensToInsert,
                \substr($propertyType, 0, 1) === '?',
            );
        }
    }

    private function isDoctrineEntity(Tokens $tokens, int $index): bool
    {
        $phpDocIndex = $tokens->getPrevNonWhitespace($index);
        \assert(\is_int($phpDocIndex));

        if (!$tokens[$phpDocIndex]->isGivenKind(\T_DOC_COMMENT)) {
            return false;
        }

        $docBlock = new DocBlock($tokens[$phpDocIndex]->getContent());

        foreach ($docBlock->getAnnotations() as $annotation) {
            if (Preg::match('/\\*\\h+(@Document|@Entity|@Mapping\\\\Entity|@ODM\\\\Document|@ORM\\\\Entity|@ORM\\\\Mapping\\\\Entity)/', $annotation->getContent())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, int> $properties
     */
    private function getPropertyIndex(Tokens $tokens, array $properties, int $assignmentIndex): ?int
    {
        $propertyNameIndex = $tokens->getPrevTokenOfKind($assignmentIndex, [[\T_STRING]]);
        \assert(\is_int($propertyNameIndex));

        $propertyName = $tokens[$propertyNameIndex]->getContent();

        foreach ($properties as $name => $index) {
            if ($name !== $propertyName) {
                continue;
            }

            return $index;
        }

        return null;
    }

    private function isPropertyToPromote(Tokens $tokens, ?int $propertyIndex, bool $isDoctrineEntity): bool
    {
        if ($propertyIndex === null) {
            return !$this->promoteOnlyExistingProperties;
        }

        if (!$isDoctrineEntity) {
            return true;
        }

        $phpDocIndex = $tokens->getPrevTokenOfKind($propertyIndex, [[\T_DOC_COMMENT]]);
        \assert(\is_int($phpDocIndex));

        $variableIndex = $tokens->getNextTokenOfKind($phpDocIndex, ['{', [\T_VARIABLE]]);

        if ($variableIndex !== $propertyIndex) {
            return true;
        }

        $docBlock = new DocBlock($tokens[$phpDocIndex]->getContent());

        return \count($docBlock->getAnnotations()) === 0;
    }

    private function getType(Tokens $tokens, ?int $variableIndex): string
    {
        if ($variableIndex === null) {
            return '';
        }

        $index = $tokens->getPrevTokenOfKind($variableIndex, ['(', ',', [\T_PRIVATE], [\T_PROTECTED], [\T_PUBLIC], [\T_VAR], [CT::T_ATTRIBUTE_CLOSE]]);
        \assert(\is_int($index));

        $index = $tokens->getNextMeaningfulToken($index);
        \assert(\is_int($index));

        $type = '';
        while ($index < $variableIndex) {
            $type .= $tokens[$index]->getContent();

            $index = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($index));
        }

        return $type;
    }

    private function typesAllowPromoting(string $propertyType, string $parameterType): bool
    {
        if ($propertyType === '') {
            return true;
        }

        if (\substr($propertyType, 0, 1) === '?') {
            $propertyType = \substr($propertyType, 1);
        }

        if (\substr($parameterType, 0, 1) === '?') {
            $parameterType = \substr($parameterType, 1);
        }

        return \strtolower($propertyType) === \strtolower($parameterType);
    }

    /**
     * @return array<string, int>
     */
    private function getClassProperties(Tokens $tokens, int $classIndex): array
    {
        $properties = [];
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        foreach ($tokensAnalyzer->getClassyElements() as $index => $element) {
            if ($element['classIndex'] !== $classIndex) {
                continue;
            }
            if ($element['type'] !== 'property') {
                continue;
            }

            $properties[\substr($element['token']->getContent(), 1)] = $index;
        }

        return $properties;
    }

    /**
     * @return list<Token>
     */
    private function removePropertyAndReturnTokensToInsert(Tokens $tokens, ?int $propertyIndex): array
    {
        if ($propertyIndex === null) {
            return [new Token([\T_PUBLIC, 'public'])];
        }

        $visibilityIndex = $tokens->getPrevTokenOfKind($propertyIndex, [[\T_PRIVATE], [\T_PROTECTED], [\T_PUBLIC], [\T_VAR]]);
        \assert(\is_int($visibilityIndex));

        $prevPropertyIndex = $this->getTokenOfKindSibling($tokens, -1, $propertyIndex, ['{', '}', ';', ',']);
        $nextPropertyIndex = $this->getTokenOfKindSibling($tokens, 1, $propertyIndex, [';', ',']);

        $removeFrom = $tokens->getTokenNotOfKindSibling($prevPropertyIndex, 1, [[\T_WHITESPACE], [\T_COMMENT]]);
        \assert(\is_int($removeFrom));
        $removeTo = $nextPropertyIndex;
        if ($tokens[$prevPropertyIndex]->equals(',')) {
            $removeFrom = $prevPropertyIndex;
            $removeTo = $propertyIndex;
        } elseif ($tokens[$nextPropertyIndex]->equals(',')) {
            $removeFrom = $tokens->getPrevMeaningfulToken($propertyIndex);
            \assert(\is_int($removeFrom));
            $removeFrom++;
        }

        $tokensToInsert = [];
        for ($index = $removeFrom; $index <= $visibilityIndex - 1; $index++) {
            $tokensToInsert[] = $tokens[$index];
        }

        $visibilityToken = $tokens[$visibilityIndex];
        if ($tokens[$visibilityIndex]->isGivenKind(\T_VAR)) {
            $visibilityToken = new Token([\T_PUBLIC, 'public']);
        }
        $tokensToInsert[] = $visibilityToken;

        $tokens->clearRange($removeFrom + 1, $removeTo);
        TokenRemover::removeWithLinesIfPossible($tokens, $removeFrom);

        return $tokensToInsert;
    }

    /**
     * @param list<string> $tokenKinds
     */
    private function getTokenOfKindSibling(Tokens $tokens, int $direction, int $index, array $tokenKinds): int
    {
        $index += $direction;

        while (!$tokens[$index]->equalsAny($tokenKinds)) {
            $blockType = Tokens::detectBlockType($tokens[$index]);

            if ($blockType !== null) {
                if ($blockType['isStart']) {
                    $index = $tokens->findBlockEnd($blockType['type'], $index);
                } else {
                    $index = $tokens->findBlockStart($blockType['type'], $index);
                }
            }

            $index += $direction;
        }

        return $index;
    }

    private function renameVariable(Tokens $tokens, int $constructorIndex, string $oldName, string $newName): void
    {
        $parenthesesOpenIndex = $tokens->getNextTokenOfKind($constructorIndex, ['(']);
        \assert(\is_int($parenthesesOpenIndex));
        $parenthesesCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $parenthesesOpenIndex);
        $braceOpenIndex = $tokens->getNextTokenOfKind($parenthesesCloseIndex, ['{']);
        \assert(\is_int($braceOpenIndex));
        $braceCloseIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $braceOpenIndex);

        for ($index = $parenthesesOpenIndex; $index < $braceCloseIndex; $index++) {
            if ($tokens[$index]->equals([\T_VARIABLE, $oldName])) {
                $tokens[$index] = new Token([\T_VARIABLE, $newName]);
            }
        }
    }

    private function removeAssignment(Tokens $tokens, int $variableAssignmentIndex): void
    {
        $thisIndex = $tokens->getPrevTokenOfKind($variableAssignmentIndex, [[\T_VARIABLE]]);
        \assert(\is_int($thisIndex));

        $propertyEndIndex = $tokens->getNextTokenOfKind($variableAssignmentIndex, [';']);
        \assert(\is_int($propertyEndIndex));

        $tokens->clearRange($thisIndex + 1, $propertyEndIndex);
        TokenRemover::removeWithLinesIfPossible($tokens, $thisIndex);
    }

    /**
     * @param list<Token> $tokensToInsert
     */
    private function updateParameterSignature(Tokens $tokens, int $constructorParameterIndex, array $tokensToInsert, bool $makeTypeNullable): void
    {
        $prevElementIndex = $tokens->getPrevTokenOfKind($constructorParameterIndex, ['(', ',', [CT::T_ATTRIBUTE_CLOSE]]);
        \assert(\is_int($prevElementIndex));

        $propertyStartIndex = $tokens->getNextMeaningfulToken($prevElementIndex);
        \assert(\is_int($propertyStartIndex));

        foreach ($tokensToInsert as $index => $token) {
            if ($token->isGivenKind(\T_PUBLIC)) {
                $tokensToInsert[$index] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, $token->getContent()]);
            } elseif ($token->isGivenKind(\T_PROTECTED)) {
                $tokensToInsert[$index] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, $token->getContent()]);
            } elseif ($token->isGivenKind(\T_PRIVATE)) {
                $tokensToInsert[$index] = new Token([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, $token->getContent()]);
            }
        }
        $tokensToInsert[] = new Token([\T_WHITESPACE, ' ']);

        if ($makeTypeNullable && !$tokens[$propertyStartIndex]->isGivenKind(CT::T_NULLABLE_TYPE)) {
            $tokensToInsert[] = new Token([CT::T_NULLABLE_TYPE, '?']);
        }

        $this->tokensToInsert[$propertyStartIndex] = $tokensToInsert;
    }
}
