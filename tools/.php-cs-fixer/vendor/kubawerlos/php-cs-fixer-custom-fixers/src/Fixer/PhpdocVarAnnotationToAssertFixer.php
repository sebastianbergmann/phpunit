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

use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixerCustomFixers\TokenRemover;

/**
 * @no-named-arguments
 */
final class PhpdocVarAnnotationToAssertFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts `@var` annotations to `assert` calls when used in assignments.',
            [new CodeSample('<?php
/** @var string $x */
$x = getValue();
')],
            '',
        );
    }

    /**
     * Must run before NativeFunctionInvocationFixer, ReturnAssignmentFixer.
     */
    public function getPriority(): int
    {
        return 2;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_DOC_COMMENT, \T_VARIABLE]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($docCommentIndex = $tokens->count() - 1; $docCommentIndex > 0; $docCommentIndex--) {
            if (!$tokens[$docCommentIndex]->isGivenKind([\T_DOC_COMMENT])) {
                continue;
            }

            $variableIndex = $this->getVariableIndex($tokens, $docCommentIndex);
            if ($variableIndex === null) {
                continue;
            }

            $assertTokens = $this->getAssertTokens($tokens, $docCommentIndex, $tokens[$variableIndex]->getContent());
            if ($assertTokens === null) {
                continue;
            }

            $expressionEndIndex = $this->getExpressionEnd($tokens, $variableIndex);

            if (!$this->canBePlacedAfterExpression($tokens, $expressionEndIndex)) {
                continue;
            }

            if ($tokens[$variableIndex - 1]->isWhitespace()) {
                \array_unshift($assertTokens, new Token([\T_WHITESPACE, $tokens[$variableIndex - 1]->getContent()]));
            }

            $tokens->insertAt($expressionEndIndex + 1, $assertTokens);

            TokenRemover::removeWithLinesIfPossible($tokens, $docCommentIndex);
        }
    }

    private function getVariableIndex(Tokens $tokens, int $docCommentIndex): ?int
    {
        $prevIndex = $tokens->getPrevMeaningfulToken($docCommentIndex);
        if (!$tokens[$prevIndex]->equalsAny([';', '{', '}', [\T_OPEN_TAG]])) {
            return null;
        }

        $variableIndex = $tokens->getNextMeaningfulToken($docCommentIndex);
        if ($variableIndex === null) {
            return null;
        }
        if (!$tokens[$variableIndex]->isGivenKind([\T_VARIABLE])) {
            return null;
        }

        $assignmentIndex = $tokens->getNextMeaningfulToken($variableIndex);
        \assert(\is_int($assignmentIndex));

        if (!$tokens[$assignmentIndex]->equals('=')) {
            return null;
        }

        return $variableIndex;
    }

    /**
     * @return null|list<Token>
     */
    private function getAssertTokens(Tokens $tokens, int $docCommentIndex, string $variableName): ?array
    {
        $annotation = $this->getAnnotationForVariable($tokens, $docCommentIndex, $variableName);
        if ($annotation === null) {
            return null;
        }

        $typeExpression = $annotation->getTypeExpression();
        if ($typeExpression === null) {
            return null;
        }

        $assertCode = '<?php assert(';

        $assertions = [];
        foreach ($typeExpression->getTypes() as $type) {
            if (\substr($type, 0, 1) === '?') {
                $assertions['null'] = $this->getCodeForType('null', $variableName);
                $type = \substr($type, 1);
            }
            $assertions[$type] = $this->getCodeForType($type, $variableName);
        }

        try {
            $tokens = Tokens::fromCode($assertCode . \implode(' || ', $assertions) . ');');
        } catch (\ParseError $exception) {
            return null;
        }

        /** @var list<Token> $arrayTokens */
        $arrayTokens = $tokens->toArray();

        return \array_slice($arrayTokens, 1);
    }

    private function getAnnotationForVariable(Tokens $tokens, int $docCommentIndex, string $variableName): ?Annotation
    {
        $docBlock = new DocBlock($tokens[$docCommentIndex]->getContent());

        if (\count($docBlock->getAnnotations()) !== 1) {
            return null;
        }

        $varAnnotations = $docBlock->getAnnotationsOfType('var');
        if (\count($varAnnotations) !== 1) {
            return null;
        }

        $varAnnotation = \reset($varAnnotations);

        if ($varAnnotation->getVariableName() !== $variableName) {
            return null;
        }

        return $varAnnotation;
    }

    private function getCodeForType(string $type, string $variableName): string
    {
        $typesMap = [
            'array' => 'is_array',
            'bool' => 'is_bool',
            'boolean' => 'is_bool',
            'callable' => 'is_callable',
            'double' => 'is_float',
            'float' => 'is_float',
            'int' => 'is_int',
            'integer' => 'is_int',
            'iterable' => 'is_iterable',
            'null' => 'is_null',
            'object' => 'is_object',
            'resource' => 'is_resource',
            'string' => 'is_string',
        ];

        if (\array_key_exists(\strtolower($type), $typesMap)) {
            return \sprintf('%s(%s)', $typesMap[\strtolower($type)], $variableName);
        }

        return \sprintf('%s instanceof %s', $variableName, $type);
    }

    private function getExpressionEnd(Tokens $tokens, int $index): int
    {
        while (!$tokens[$index]->equals(';')) {
            $index = $tokens->getNextMeaningfulToken($index);
            \assert(\is_int($index));

            $blockType = Tokens::detectBlockType($tokens[$index]);
            if ($blockType !== null && $blockType['isStart']) {
                $index = $tokens->findBlockEnd($blockType['type'], $index);
            }
        }

        return $index;
    }

    private function canBePlacedAfterExpression(Tokens $tokens, int $expressionEndIndex): bool
    {
        $afterExpressionIndex = $tokens->getNextMeaningfulToken($expressionEndIndex);

        if ($afterExpressionIndex === null) {
            return true;
        }

        if ($tokens[$afterExpressionIndex]->isGivenKind(\T_NS_SEPARATOR)) {
            $afterExpressionIndex = $tokens->getNextMeaningfulToken($afterExpressionIndex);
        }

        return !$tokens[$afterExpressionIndex]->equals([\T_STRING, 'assert'], false);
    }
}
