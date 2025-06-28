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

use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{allow_linebreak?: bool}
 * @phpstan-type _Config array{allow_linebreak: bool}
 *
 * @no-named-arguments
 */
final class SingleSpaceAfterStatementFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /** @var list<int> */
    private array $tokens = [
        \T_ABSTRACT,
        \T_AS,
        \T_BREAK,
        \T_CASE,
        \T_CATCH,
        \T_CLASS,
        \T_CLONE,
        \T_CONST,
        \T_CONTINUE,
        \T_DO,
        \T_ECHO,
        \T_ELSE,
        \T_ELSEIF,
        \T_EXTENDS,
        \T_FINAL,
        \T_FINALLY,
        \T_FOR,
        \T_FOREACH,
        \T_FUNCTION,
        \T_GLOBAL,
        \T_GOTO,
        \T_IF,
        \T_IMPLEMENTS,
        \T_INCLUDE,
        \T_INCLUDE_ONCE,
        \T_INSTANCEOF,
        \T_INSTEADOF,
        \T_INTERFACE,
        \T_NAMESPACE,
        \T_NEW,
        \T_PRINT,
        \T_PRIVATE,
        \T_PROTECTED,
        \T_PUBLIC,
        \T_REQUIRE,
        \T_REQUIRE_ONCE,
        \T_RETURN,
        \T_SWITCH,
        \T_THROW,
        \T_TRAIT,
        \T_TRY,
        \T_USE,
        \T_VAR,
        \T_WHILE,
        \T_YIELD,
        \T_YIELD_FROM,
        CT::T_CONST_IMPORT,
        CT::T_FUNCTION_IMPORT,
        CT::T_USE_TRAIT,
        CT::T_USE_LAMBDA,
    ];

    private bool $allowLinebreak = false;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Statements not followed by a semicolon must be followed by a single space.',
            [new CodeSample("<?php\n\$foo = new    Foo();\necho\$foo->bar();\n")],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('allow_linebreak', 'whether to allow statement followed by linebreak'))
                ->setAllowedTypes(['bool'])
                ->setDefault($this->allowLinebreak)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('allow_linebreak', $configuration)) {
            $this->allowLinebreak = $configuration['allow_linebreak'];
        }
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound($this->tokens);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind($this->tokens)) {
                continue;
            }

            if (!$this->canAddSpaceAfter($tokens, $index)) {
                continue;
            }

            $tokens->ensureWhitespaceAtIndex($index + 1, 0, ' ');
        }
    }

    private function canAddSpaceAfter(Tokens $tokens, int $index): bool
    {
        if ($tokens[$index + 1]->isGivenKind(\T_WHITESPACE)) {
            return !$this->allowLinebreak || !Preg::match('/\\R/', $tokens[$index + 1]->getContent());
        }

        if ($tokens[$index]->isGivenKind(\T_CLASS) && $tokens[$index + 1]->equals('(')) {
            return false;
        }

        return !\in_array($tokens[$index + 1]->getContent(), [';', ':'], true);
    }
}
