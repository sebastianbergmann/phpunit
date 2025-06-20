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
use PhpCsFixer\Fixer\Phpdoc\PhpdocArrayTypeFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @deprecated
 *
 * @no-named-arguments
 */
final class PhpdocArrayStyleFixer extends AbstractFixer implements DeprecatedFixerInterface
{
    private PhpdocArrayTypeFixer $phpdocArrayTypeFixer;

    public function __construct()
    {
        $this->phpdocArrayTypeFixer = new PhpdocArrayTypeFixer();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Generic array style should be used in PHPDoc.',
            [
                new CodeSample(
                    '<?php
/**
 * @return int[]
 */
 function foo() { return [1, 2]; }
',
                ),
            ],
            '',
        );
    }

    /**
     * Must run before PhpdocAlignFixer, PhpdocTypeListFixer, PhpdocTypesOrderFixer.
     */
    public function getPriority(): int
    {
        return $this->phpdocArrayTypeFixer->getPriority();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->phpdocArrayTypeFixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->phpdocArrayTypeFixer->fix($file, $tokens);
    }

    /**
     * @return list<string>
     */
    public function getSuccessorsNames(): array
    {
        return [$this->phpdocArrayTypeFixer->getName()];
    }
}
