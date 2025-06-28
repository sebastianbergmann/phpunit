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

use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Indicator\PhpUnitTestCaseIndicator;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FullyQualifiedNameAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments
 */
final class PhpUnitRequiresConstraintFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Assertions and attributes for PHP and PHPUnit versions must have explicit version constraint and space after comparison operator.',
            [new CodeSample(
                <<<'PHP'
                    <?php
                    class MyTest extends TestCase {
                        /**
                         * @requires PHP 8.1
                         */
                        public function testFoo() {}

                        /**
                         * @requires PHP <8.3
                         */
                        public function testBar() {}

                        #[\PHPUnit\Framework\Attributes\RequiresPhpunit('12.0')]
                        public function testBaz() {}
                    }

                    PHP,
            )],
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_EXTENDS, \T_FUNCTION]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $phpUnitTestCaseIndicator = new PhpUnitTestCaseIndicator();

        /** @var list<int> $indices */
        foreach ($phpUnitTestCaseIndicator->findPhpUnitClasses($tokens) as $indices) {
            $this->fixClass($tokens, $indices[0], $indices[1]);
        }
    }

    private function fixClass(Tokens $tokens, int $index, int $endIndex): void
    {
        while ($index < $endIndex) {
            $index = $tokens->getNextTokenOfKind($index, ['{', [\T_FUNCTION]]);
            if ($index === null || $index >= $endIndex) {
                return;
            }

            if ($tokens[$index]->equals('{')) {
                $index = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                continue;
            }

            self::fixMethod($tokens, $index);
        }
    }

    private static function fixMethod(Tokens $tokens, int $index): void
    {
        $index = $tokens->getPrevTokenOfKind($index, [';', [\T_DOC_COMMENT], [CT::T_ATTRIBUTE_CLOSE]]);
        if ($index === null || $tokens[$index]->equals(';')) {
            return;
        }

        if ($tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
            self::fixPhpDoc($tokens, $index);
        }

        if ($tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
            self::fixAttribute($tokens, $index);
        }
    }

    private static function fixPhpDoc(Tokens $tokens, int $index): void
    {
        $tokens[$index] = new Token([
            \T_DOC_COMMENT,
            Preg::replaceCallback(
                '/(@requires\\s+\\S+\\s+)(.+?)(\\s*)$/m',
                static function (array $matches): string {
                    \assert(\is_string($matches[1]));
                    \assert(\is_string($matches[2]));
                    \assert(\is_string($matches[3]));

                    return $matches[1] . self::fixString($matches[2]) . $matches[3];
                },
                $tokens[$index]->getContent(),
            ),
        ]);
    }

    private static function fixAttribute(Tokens $tokens, int $index): void
    {
        $fullyQualifiedNameAnalyzer = new FullyQualifiedNameAnalyzer($tokens);
        foreach (AttributeAnalyzer::collect($tokens, $tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $index)) as $attributeAnalysis) {
            foreach ($attributeAnalysis->getAttributes() as $attribute) {
                $attributeName = \strtolower($fullyQualifiedNameAnalyzer->getFullyQualifiedName($attribute['name'], $attribute['start'], NamespaceUseAnalysis::TYPE_CLASS));
                if (
                    $attributeName === 'phpunit\\framework\\attributes\\requiresphp'
                    || $attributeName === 'phpunit\\framework\\attributes\\requiresphpunit'
                ) {
                    $stringIndex = $tokens->getPrevMeaningfulToken($attribute['end']);
                    \assert(\is_int($stringIndex));
                    if (!$tokens[$stringIndex]->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                        continue;
                    }

                    $openParenthesisIndex = $tokens->getPrevMeaningfulToken($stringIndex);
                    \assert(\is_int($openParenthesisIndex));
                    if (!$tokens[$openParenthesisIndex]->equals('(')) {
                        continue;
                    }

                    $quote = \substr($tokens[$stringIndex]->getContent(), -1, 1);
                    $tokens[$stringIndex] = new Token([
                        \T_CONSTANT_ENCAPSED_STRING,
                        $quote . self::fixString(\substr($tokens[$stringIndex]->getContent(), 1, -1)) . $quote,
                    ]);
                }
            }
        }
    }

    private static function fixString(string $string): string
    {
        if (Preg::match('/^[\\d\\.-]+(dev|(RC|alpha|beta)[\\d\\.])?$/', $string)) {
            $string = '>=' . $string;
        }

        return Preg::replace('/^([<>=!]{0,2})\\s*([\\d\\.-]+(dev|(RC|alpha|beta)[\\d\\.])?)$/', '$1 $2', $string);
    }
}
