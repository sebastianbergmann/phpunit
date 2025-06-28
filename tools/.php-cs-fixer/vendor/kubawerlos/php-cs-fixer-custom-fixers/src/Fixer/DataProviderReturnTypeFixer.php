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

use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDataProviderReturnTypeFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @deprecated
 *
 * @no-named-arguments
 */
final class DataProviderReturnTypeFixer extends AbstractFixer implements DeprecatedFixerInterface
{
    private PhpUnitDataProviderReturnTypeFixer $phpUnitDataProviderReturnTypeFixer;

    public function __construct()
    {
        $this->phpUnitDataProviderReturnTypeFixer = new PhpUnitDataProviderReturnTypeFixer();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            $this->phpUnitDataProviderReturnTypeFixer->getDefinition()->getSummary(),
            [
                new CodeSample(
                    '<?php
class FooTest extends TestCase {
    /**
     * @dataProvider provideSomethingCases
     */
    public function testSomething($expected, $actual) {}
    public function provideSomethingCases(): array {}
}
',
                ),
            ],
            '',
            'when relying on signature of data provider',
        );
    }

    /**
     * Must run before ReturnTypeDeclarationFixer.
     */
    public function getPriority(): int
    {
        return $this->phpUnitDataProviderReturnTypeFixer->getPriority();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->phpUnitDataProviderReturnTypeFixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return $this->phpUnitDataProviderReturnTypeFixer->isRisky();
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->phpUnitDataProviderReturnTypeFixer->fix($file, $tokens);
    }

    /**
     * @return list<string>
     */
    public function getSuccessorsNames(): array
    {
        return [$this->phpUnitDataProviderReturnTypeFixer->getName()];
    }
}
