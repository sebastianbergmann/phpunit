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

use PhpCsFixer\Linter\LintingResultInterface;

/**
 * @internal
 *
 * @extends \Iterator<mixed, \SplFileInfo>
 *
 * @author Greg Korba <greg@codito.dev>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface LintingResultAwareFileIteratorInterface extends \Iterator
{
    public function currentLintingResult(): ?LintingResultInterface;
}
