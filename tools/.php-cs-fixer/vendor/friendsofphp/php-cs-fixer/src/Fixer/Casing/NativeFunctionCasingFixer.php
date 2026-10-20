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
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NativeFunctionCasingFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Function defined by PHP should be called using the correct casing.',
            [new CodeSample("<?php\nSTRLEN(\$str);\n")],
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run after FunctionToConstantFixer, NoUselessSprintfFixer, PowToExponentiationFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_STRING);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $functionsAnalyzer = new FunctionsAnalyzer();

        static $nativeFunctionNames = null;

        if (null === $nativeFunctionNames) {
            $nativeFunctionNames = $this->getNativeFunctionNames();
        }

        for ($index = 0, $count = $tokens->count(); $index < $count; ++$index) {
            // test if we are at a function all
            if (!$functionsAnalyzer->isGlobalFunctionCall($tokens, $index)) {
                continue;
            }

            // test if the function call is to a native PHP function
            $lower = strtolower($tokens[$index]->getContent());
            if (!\array_key_exists($lower, $nativeFunctionNames)) {
                continue;
            }

            $tokens[$index] = new Token([\T_STRING, $nativeFunctionNames[$lower]]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getNativeFunctionNames(): array
    {
        $allFunctions = get_defined_functions();
        $functions = [];
        foreach ($allFunctions['internal'] as $function) {
            $functions[strtolower($function)] = $function;
        }

        return $functions;
    }
}
