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

namespace PhpCsFixer;

use PhpCsFixer\Runner\Parallel\ParallelConfig;

/**
 * @author Greg Korba <greg@codito.dev>
 *
 * @TODO 4.0 Include parallel runner config in main ConfigInterface
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface ParallelAwareConfigInterface extends ConfigInterface
{
    public function getParallelConfig(): ParallelConfig;

    /**
     * @return $this
     */
    public function setParallelConfig(ParallelConfig $config): ConfigInterface;
}
