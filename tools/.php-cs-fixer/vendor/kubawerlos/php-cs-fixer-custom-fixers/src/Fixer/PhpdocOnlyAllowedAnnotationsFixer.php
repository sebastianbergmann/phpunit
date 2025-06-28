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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{elements?: list<string>}
 * @phpstan-type _Config array{elements: list<string>}
 *
 * @no-named-arguments
 */
final class PhpdocOnlyAllowedAnnotationsFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /** @var list<string> */
    private array $elements = [];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Only the listed annotations are allowed in PHPDoc.',
            [new CodeSample(
                '<?php
/**
 * @author John Doe
 * @package foo
 * @subpackage bar
 * @version 1.0
 */
function foo_bar() {}
',
                ['elements' => ['author', 'version']],
            )],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('elements', 'list of annotations to keep in PHPDoc'))
                ->setAllowedTypes(['array'])
                ->setDefault($this->elements)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, list<string>> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('elements', $configuration)) {
            $this->elements = $configuration['elements'];
        }
    }

    /**
     * Must run before NoEmptyPhpdocFixer, PhpdocTagNoNamedArgumentsFixer.
     * Must run after CommentToPhpdocFixer.
     */
    public function getPriority(): int
    {
        return 4;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }

            $docBlock = new DocBlock($tokens[$index]->getContent());

            foreach ($docBlock->getAnnotations() as $annotation) {
                if (
                    Preg::match('/@([a-zA-Z0-9_\\-\\\\]+)/', $annotation->getContent(), $matches)
                    && \in_array($matches[1], $this->elements, true)
                ) {
                    continue;
                }

                $annotation->remove();
            }

            if ($docBlock->getContent() === '') {
                $tokens->clearTokenAndMergeSurroundingWhitespace($index);
                continue;
            }

            $tokens[$index] = new Token([\T_DOC_COMMENT, $docBlock->getContent()]);
        }
    }
}
