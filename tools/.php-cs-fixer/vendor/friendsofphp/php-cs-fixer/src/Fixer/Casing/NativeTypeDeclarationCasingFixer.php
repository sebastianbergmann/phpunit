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
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NativeTypeDeclarationCasingFixer extends AbstractFixer
{
    /**
     * https://secure.php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.
     *
     * self     PHP 5.0
     * array    PHP 5.1
     * callable PHP 5.4
     * bool     PHP 7.0
     * float    PHP 7.0
     * int      PHP 7.0
     * string   PHP 7.0
     * iterable PHP 7.1
     * void     PHP 7.1
     * object   PHP 7.2
     * static   PHP 8.0 (return type only)
     * mixed    PHP 8.0
     * false    PHP 8.0 (union return type only)
     * null     PHP 8.0 (union return type only)
     * never    PHP 8.1 (return type only)
     * true     PHP 8.2 (standalone type: https://wiki.php.net/rfc/true-type)
     * false    PHP 8.2 (standalone type: https://wiki.php.net/rfc/null-false-standalone-types)
     * null     PHP 8.2 (standalone type: https://wiki.php.net/rfc/null-false-standalone-types)
     *
     * @var array<string, true>
     */
    private array $types;

    public function __construct()
    {
        parent::__construct();

        $this->types = [
            'array' => true,
            'bool' => true,
            'callable' => true,
            'float' => true,
            'int' => true,
            'iterable' => true,
            'object' => true,
            'parent' => true,
            'self' => true,
            'static' => true,
            'string' => true,
            'void' => true,
        ];

        if (\PHP_VERSION_ID >= 8_00_00) {
            $this->types['false'] = true;
            $this->types['mixed'] = true;
            $this->types['null'] = true;
        }

        if (\PHP_VERSION_ID >= 8_01_00) {
            $this->types['never'] = true;
        }

        if (\PHP_VERSION_ID >= 8_02_00) {
            $this->types['true'] = true;
        }
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Native type declarations should be used in the correct case.',
            [
                new CodeSample(
                    "<?php\nclass Bar {\n    public function Foo(CALLABLE \$bar): INT\n    {\n        return 1;\n    }\n}\n",
                ),
                new VersionSpecificCodeSample(
                    "<?php\nclass Foo\n{\n    const INT BAR = 1;\n}\n",
                    new VersionSpecification(8_03_00),
                ),
            ],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        $classyFound = $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());

        return
            $tokens->isAnyTokenKindsFound([\T_FUNCTION, \T_FN])
            || ($classyFound && $tokens->isTokenKindFound(\T_STRING))
            || (
                \PHP_VERSION_ID >= 8_03_00
                && $tokens->isTokenKindFound(\T_CONST)
                && $classyFound
            );
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            $content = $token->getContent();
            $lowercaseContent = strtolower($content);
            if ($content === $lowercaseContent) {
                continue;
            }
            if (!isset($this->types[$lowercaseContent])) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$prevIndex]->equals('=') || $tokens[$prevIndex]->isGivenKind([\T_CASE, \T_OBJECT_OPERATOR, \T_DOUBLE_COLON, \T_NS_SEPARATOR])) {
                continue;
            }

            $nextIndex = $tokens->getNextMeaningfulToken($index);
            if ($tokens[$nextIndex]->equals('=') || $tokens[$nextIndex]->isGivenKind(\T_NS_SEPARATOR)) {
                continue;
            }

            if (
                !$tokens[$prevIndex]->isGivenKind([\T_CONST, CT::T_NULLABLE_TYPE, CT::T_TYPE_ALTERNATION, CT::T_TYPE_COLON])
                && !$tokens[$nextIndex]->isGivenKind([\T_VARIABLE, CT::T_TYPE_ALTERNATION])
            ) {
                continue;
            }

            $tokens[$index] = new Token([$token->getId(), $lowercaseContent]);
        }
    }
}
