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
final class MatchAnalysis extends AbstractControlCaseStructuresAnalysis
{
    private ?DefaultAnalysis $defaultAnalysis;

    public function __construct(int $index, int $open, int $close, ?DefaultAnalysis $defaultAnalysis)
    {
        parent::__construct($index, $open, $close);

        $this->defaultAnalysis = $defaultAnalysis;
    }

    public function getDefaultAnalysis(): ?DefaultAnalysis
    {
        return $this->defaultAnalysis;
    }
}
