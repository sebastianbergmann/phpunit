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

namespace PhpCsFixer\Linter;

use Symfony\Component\Process\Process;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProcessLinterProcessBuilder
{
    private string $executable;

    /**
     * @param string $executable PHP executable
     */
    public function __construct(string $executable)
    {
        $this->executable = $executable;
    }

    public function build(string $path): Process
    {
        return new Process([
            $this->executable,
            '-l',
            $path,
        ]);
    }
}
