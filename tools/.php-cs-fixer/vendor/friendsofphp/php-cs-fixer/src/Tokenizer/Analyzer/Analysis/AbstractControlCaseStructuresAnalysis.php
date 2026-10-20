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

namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractControlCaseStructuresAnalysis
{
    private int $index;

    private int $open;

    private int $close;

    public function __construct(int $index, int $open, int $close)
    {
        $this->index = $index;
        $this->open = $open;
        $this->close = $close;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getOpenIndex(): int
    {
        return $this->open;
    }

    public function getCloseIndex(): int
    {
        return $this->close;
    }
}
