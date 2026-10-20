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

namespace PhpCsFixer\Runner;

use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\Linter\LintingResultInterface;

/**
 * @internal
 *
 * @extends \IteratorIterator<mixed, \SplFileInfo, \Traversable<\SplFileInfo>>
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class LintingFileIterator extends \IteratorIterator implements LintingResultAwareFileIteratorInterface
{
    private ?LintingResultInterface $currentResult = null;

    private LinterInterface $linter;

    /**
     * @param \Traversable<mixed, \SplFileInfo> $iterator
     */
    public function __construct(\Traversable $iterator, LinterInterface $linter)
    {
        parent::__construct($iterator);

        $this->linter = $linter;
    }

    public function currentLintingResult(): ?LintingResultInterface
    {
        return $this->currentResult;
    }

    public function next(): void
    {
        parent::next();

        $this->currentResult = $this->valid() ? $this->handleItem($this->current()) : null;
    }

    public function rewind(): void
    {
        parent::rewind();

        $this->currentResult = $this->valid() ? $this->handleItem($this->current()) : null;
    }

    private function handleItem(\SplFileInfo $file): LintingResultInterface
    {
        return $this->linter->lintFile($file->getRealPath());
    }
}
