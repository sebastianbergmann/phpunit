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
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderNameFixer;
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
 * @phpstan-type _InputConfig array{prefix?: string, suffix?: string}
 * @phpstan-type _Config array{prefix: string, suffix: string}
 *
 * @no-named-arguments
 */
final class DataProviderNameFixer extends AbstractFixer implements ConfigurableFixerInterface, DeprecatedFixerInterface
{
    private PhpUnitDataProviderNameFixer $phpUnitDataProviderNameFixer;
    private string $prefix = 'provide';
    private string $suffix = 'Cases';

    public function __construct()
    {
        $this->phpUnitDataProviderNameFixer = new PhpUnitDataProviderNameFixer();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            $this->phpUnitDataProviderNameFixer->getDefinition()->getSummary(),
            [
                new CodeSample(
                    '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider dataProvider
     */
    public function testSomething($expected, $actual) {}
    public function dataProvider() {}
}
',
                ),
            ],
            '',
            'when relying on name of data provider function',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('prefix', 'prefix that replaces "test"'))
                ->setAllowedTypes(['string'])
                ->setDefault($this->prefix)
                ->getOption(),
            (new FixerOptionBuilder('suffix', 'suffix to be added at the end"'))
                ->setAllowedTypes(['string'])
                ->setDefault($this->suffix)
                ->getOption(),
        ]);
    }

    public function configure(array $configuration): void
    {
        $this->phpUnitDataProviderNameFixer->configure($configuration);
    }

    public function getPriority(): int
    {
        return $this->phpUnitDataProviderNameFixer->getPriority();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->phpUnitDataProviderNameFixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return $this->phpUnitDataProviderNameFixer->isRisky();
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->phpUnitDataProviderNameFixer->fix($file, $tokens);
    }

    /**
     * @return list<string>
     */
    public function getSuccessorsNames(): array
    {
        return [$this->phpUnitDataProviderNameFixer->getName()];
    }
}
