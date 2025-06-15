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
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderStaticFixer;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @deprecated
 *
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{force?: bool}
 * @phpstan-type _Config array{force: bool}
 *
 * @no-named-arguments
 */
final class DataProviderStaticFixer extends AbstractFixer implements ConfigurableFixerInterface, DeprecatedFixerInterface
{
    private bool $force = false;
    private PhpUnitDataProviderStaticFixer $phpUnitDataProviderStaticFixer;

    public function __construct()
    {
        $this->phpUnitDataProviderStaticFixer = new PhpUnitDataProviderStaticFixer();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            $this->phpUnitDataProviderStaticFixer->getDefinition()->getSummary(),
            [
                new CodeSample(
                    '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideSomethingCases
     */
    public function testSomething($expected, $actual) {}
    public function provideSomethingCases() {}
}
',
                ),
            ],
            '',
            'when `force` is set to `true`',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('force', 'whether to make static data providers having dynamic class calls'))
                ->setAllowedTypes(['bool'])
                ->setDefault($this->force)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, bool> $configuration
     */
    public function configure(array $configuration): void
    {
        if (\array_key_exists('force', $configuration)) {
            $this->force = $configuration['force'];
        }
        $this->phpUnitDataProviderStaticFixer->configure(['force' => $this->force]);
    }

    public function getPriority(): int
    {
        return $this->phpUnitDataProviderStaticFixer->getPriority();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->phpUnitDataProviderStaticFixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return $this->phpUnitDataProviderStaticFixer->isRisky();
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->phpUnitDataProviderStaticFixer->fix($file, $tokens);
    }

    /**
     * @return list<string>
     */
    public function getSuccessorsNames(): array
    {
        return [$this->phpUnitDataProviderStaticFixer->getName()];
    }
}
