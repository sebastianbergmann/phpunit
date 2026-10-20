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

namespace PhpCsFixer\Differ;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FullDiffer implements DifferInterface
{
    private Differ $differ;

    public function __construct()
    {
        $this->differ = new Differ(new StrictUnifiedDiffOutputBuilder([
            'collapseRanges' => false,
            'commonLineThreshold' => 100,
            'contextLines' => 100,
            'fromFile' => 'Original',
            'toFile' => 'New',
        ]));
    }

    public function diff(string $old, string $new, ?\SplFileInfo $file = null): string
    {
        return $this->differ->diff($old, $new);
    }
}
