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

namespace PhpCsFixer\Fixer\Alias;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\ArgumentsAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class MbStrFunctionsFixer extends AbstractFixer
{
    /**
     * list of the string-related function names and their mb_ equivalent.
     *
     * @var array<
     *     string,
     *     array{
     *         alternativeName: string,
     *         argumentCount: list<int>,
     *     },
     * >
     */
    private static array $functionsMap = [
        'str_split' => ['alternativeName' => 'mb_str_split', 'argumentCount' => [1, 2, 3]],
        'stripos' => ['alternativeName' => 'mb_stripos', 'argumentCount' => [2, 3]],
        'stristr' => ['alternativeName' => 'mb_stristr', 'argumentCount' => [2, 3]],
        'strlen' => ['alternativeName' => 'mb_strlen', 'argumentCount' => [1]],
        'strpos' => ['alternativeName' => 'mb_strpos', 'argumentCount' => [2, 3]],
        'strrchr' => ['alternativeName' => 'mb_strrchr', 'argumentCount' => [2]],
        'strripos' => ['alternativeName' => 'mb_strripos', 'argumentCount' => [2, 3]],
        'strrpos' => ['alternativeName' => 'mb_strrpos', 'argumentCount' => [2, 3]],
        'strstr' => ['alternativeName' => 'mb_strstr', 'argumentCount' => [2, 3]],
        'strtolower' => ['alternativeName' => 'mb_strtolower', 'argumentCount' => [1]],
        'strtoupper' => ['alternativeName' => 'mb_strtoupper', 'argumentCount' => [1]],
        'substr' => ['alternativeName' => 'mb_substr', 'argumentCount' => [2, 3]],
        'substr_count' => ['alternativeName' => 'mb_substr_count', 'argumentCount' => [2, 3, 4]],
    ];

    /**
     * @var array<
     *     string,
     *     array{
     *         alternativeName: string,
     *         argumentCount: list<int>,
     *     },
     * >
     */
    private array $functions;

    public function __construct()
    {
        parent::__construct();

        if (\PHP_VERSION_ID >= 8_03_00) {
            self::$functionsMap['str_pad'] = ['alternativeName' => 'mb_str_pad', 'argumentCount' => [1, 2, 3, 4]];
        }

        if (\PHP_VERSION_ID >= 8_04_00) {
            self::$functionsMap['trim'] = ['alternativeName' => 'mb_trim', 'argumentCount' => [1, 2]];
            self::$functionsMap['ltrim'] = ['alternativeName' => 'mb_ltrim', 'argumentCount' => [1, 2]];
            self::$functionsMap['rtrim'] = ['alternativeName' => 'mb_rtrim', 'argumentCount' => [1, 2]];
        }

        $this->functions = array_filter(
            self::$functionsMap,
            static fn (array $mapping): bool => (new \ReflectionFunction($mapping['alternativeName']))->isInternal(),
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    public function isRisky(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Must run before NativeFunctionInvocationFixer.
     */
    public function getPriority(): int
    {
        return 2;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Replace non multibyte-safe functions with corresponding mb function.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php
                        $a = strlen($a);
                        $a = strpos($a, $b);
                        $a = strrpos($a, $b);
                        $a = substr($a, $b);
                        $a = strtolower($a);
                        $a = strtoupper($a);
                        $a = stripos($a, $b);
                        $a = strripos($a, $b);
                        $a = strstr($a, $b);
                        $a = stristr($a, $b);
                        $a = strrchr($a, $b);
                        $a = substr_count($a, $b);

                        PHP,
                ),
            ],
            null,
            'Risky when any of the functions are overridden, or when relying on the string byte size rather than its length in characters.',
        );
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $argumentsAnalyzer = new ArgumentsAnalyzer();
        $functionsAnalyzer = new FunctionsAnalyzer();

        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_STRING)) {
                continue;
            }

            $lowercasedContent = strtolower($tokens[$index]->getContent());
            if (!isset($this->functions[$lowercasedContent])) {
                continue;
            }

            // is it a global function call?
            if ($functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                $openParenthesis = $tokens->getNextMeaningfulToken($index);
                $closeParenthesis = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesis);
                $numberOfArguments = $argumentsAnalyzer->countArguments($tokens, $openParenthesis, $closeParenthesis);
                if (!\in_array($numberOfArguments, $this->functions[$lowercasedContent]['argumentCount'], true)) {
                    continue;
                }
                $tokens[$index] = new Token([\T_STRING, $this->functions[$lowercasedContent]['alternativeName']]);

                continue;
            }

            // is it a global function import?
            $functionIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$functionIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                $functionIndex = $tokens->getPrevMeaningfulToken($functionIndex);
            }
            if (!$tokens[$functionIndex]->isGivenKind(CT::T_FUNCTION_IMPORT)) {
                continue;
            }
            $tokens[$index] = new Token([\T_STRING, $this->functions[$lowercasedContent]['alternativeName']]);
        }
    }
}
