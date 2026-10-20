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

namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ExperimentalFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ClassKeywordFixer extends AbstractFixer implements ExperimentalFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Converts FQCN strings to `*::class` keywords.',
            [
                new CodeSample(
                    <<<'PHP'
                        <?php

                        $foo = 'PhpCsFixer\Tokenizer\Tokens';
                        $bar = "\PhpCsFixer\Tokenizer\Tokens";

                        PHP,
                ),
            ],
            'This rule does not have an understanding of whether a class exists in the scope of the codebase or not, relying on run-time and autoloaded classes to determine it, which makes the rule useless when running on a single file out of codebase context.',
            'Do not use it, unless you know what you are doing.',
        );
    }

    /**
     * {@inheritdoc}
     *
     * Must run before FullyQualifiedStrictTypesFixer.
     */
    public function getPriority(): int
    {
        return 8;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    public function isRisky(): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index >= 0; --$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind(\T_CONSTANT_ENCAPSED_STRING)) {
                $name = substr($token->getContent(), 1, -1);
                $name = ltrim($name, '\\');
                $name = str_replace('\\\\', '\\', $name);

                if ($this->exists($name)) {
                    $substitution = Tokens::fromCode("<?php echo \\{$name}::class;");
                    $substitution->clearRange(0, 2);
                    $substitution->clearAt($substitution->getSize() - 1);
                    $substitution->clearEmptyTokens();

                    $tokens->clearAt($index);
                    $tokens->insertAt($index, $substitution);
                }
            }
        }
    }

    private function exists(string $name): bool
    {
        if (class_exists($name) || interface_exists($name) || trait_exists($name)) {
            $rc = new \ReflectionClass($name);

            return $rc->getName() === $name;
        }

        return false;
    }
}
