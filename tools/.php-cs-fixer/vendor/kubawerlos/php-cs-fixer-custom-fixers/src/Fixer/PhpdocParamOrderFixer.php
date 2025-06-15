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
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @deprecated
 *
 * @no-named-arguments
 */
final class PhpdocParamOrderFixer extends AbstractFixer implements DeprecatedFixerInterface
{
    private \PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer $phpdocParamOrderFixer;

    public function __construct()
    {
        $this->phpdocParamOrderFixer = new \PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer();
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            $this->phpdocParamOrderFixer->getDefinition()->getSummary(),
            [new CodeSample('<?php
/**
 * @param int $b
 * @param int $a
 * @param int $c
 */
function foo($a, $b, $c) {}
')],
            '',
        );
    }

    /**
     * Must run before PhpdocAlignFixer.
     * Must run after CommentToPhpdocFixer, PhpdocAddMissingParamAnnotationFixer.
     */
    public function getPriority(): int
    {
        return $this->phpdocParamOrderFixer->getPriority();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $this->phpdocParamOrderFixer->isCandidate($tokens);
    }

    public function isRisky(): bool
    {
        return $this->phpdocParamOrderFixer->isRisky();
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        $this->phpdocParamOrderFixer->fix($file, $tokens);
    }

    /**
     * @return list<string>
     */
    public function getSuccessorsNames(): array
    {
        return [$this->phpdocParamOrderFixer->getName()];
    }
}
