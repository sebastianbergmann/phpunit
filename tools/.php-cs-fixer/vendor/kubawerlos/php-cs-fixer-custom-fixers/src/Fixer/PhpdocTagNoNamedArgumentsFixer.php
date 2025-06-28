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

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitInternalClassFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{directory?: string, description?: string}
 * @phpstan-type _Config array{directory: string, description: string}
 *
 * @no-named-arguments
 */
final class PhpdocTagNoNamedArgumentsFixer extends AbstractFixer implements ConfigurableFixerInterface, WhitespacesAwareFixerInterface
{
    private string $description = '';
    private string $directory = '';
    private WhitespacesFixerConfig $whitespacesConfig;

    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->whitespacesConfig = $config;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There must be `@no-named-arguments` tag in PHPDoc of a class/enum/interface/trait.',
            [new CodeSample(<<<'PHP'
                <?php
                class Foo
                {
                    public function bar(string $s) {}
                }

                PHP)],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('description', 'description of the tag'))
                ->setAllowedTypes(['string'])
                ->setDefault('')
                ->getOption(),
            (new FixerOptionBuilder('directory', 'directory in which apply the changes, empty value will result with current working directory (result of `getcwd` call)'))
                ->setAllowedTypes(['string'])
                ->setDefault('')
                ->getOption(),
        ]);
    }

    /**
     * @param _InputConfig $configuration
     */
    public function configure(array $configuration): void
    {
        /** @var array{directory: string, description: string} $configuration */
        $configuration = $this->getConfigurationDefinition()->resolve($configuration);

        $this->directory = $configuration['directory'];

        if ($this->directory === '') {
            $cwd = \getcwd();
            \assert(\is_string($cwd));
            $this->directory = $cwd;
        }

        if (!\is_dir($this->directory)) {
            throw new InvalidFixerConfigurationException($this->getName(), \sprintf('The directory "%s" does not exists.', $this->directory));
        }

        $this->directory = \realpath($this->directory) . \DIRECTORY_SEPARATOR;

        $this->description = $configuration['description'];
    }

    /**
     * Must run before PhpdocSeparationFixer.
     * Must run after PhpdocOnlyAllowedAnnotationsFixer.
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(Token::getClassyTokenKinds());
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        if (!\str_starts_with($file->getRealPath(), $this->directory)) {
            return;
        }

        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isClassy()) {
                continue;
            }

            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            if ($tokens[$prevIndex]->isGivenKind(\T_NEW)) {
                continue;
            }

            $this->ensureIsDocBlockWithNoNameArgumentsTag($tokens, $index);

            $docBlockIndex = $tokens->getPrevTokenOfKind($index + 2, [[\T_DOC_COMMENT]]);
            \assert(\is_int($docBlockIndex));

            $content = $tokens[$docBlockIndex]->getContent();

            $newContent = Preg::replace('/@no-named-arguments.*\\R/', \rtrim('@no-named-arguments ' . $this->description) . $this->whitespacesConfig->getLineEnding(), $content);

            if ($newContent !== $content) {
                $tokens[$docBlockIndex] = new Token([\T_DOC_COMMENT, $newContent]);
            }
        }
    }

    private function ensureIsDocBlockWithNoNameArgumentsTag(Tokens $tokens, int $index): void
    {
        /** @var callable(WhitespacesFixerConfig, Tokens, int): void $ensureIsDocBlockWithTagNoNameArguments */
        static $ensureIsDocBlockWithTagNoNameArguments;

        if ($ensureIsDocBlockWithTagNoNameArguments === null) {
            $ensureIsDocBlockWithTagNoNameArguments = \Closure::bind(
                static function (WhitespacesFixerConfig $whitespacesConfig, Tokens $tokens, int $index): void {
                    $phpUnitInternalClassFixer = new PhpUnitInternalClassFixer();
                    $phpUnitInternalClassFixer->setWhitespacesConfig($whitespacesConfig);
                    $phpUnitInternalClassFixer->ensureIsDocBlockWithAnnotation($tokens, $index, 'no-named-arguments', ['internal', 'no-named-arguments'], []);
                },
                null,
                PhpUnitInternalClassFixer::class,
            );
        }

        $ensureIsDocBlockWithTagNoNameArguments($this->whitespacesConfig, $tokens, $index);
    }
}
