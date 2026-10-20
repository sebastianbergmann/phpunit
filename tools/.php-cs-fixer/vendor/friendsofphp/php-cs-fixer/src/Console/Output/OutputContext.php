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

namespace PhpCsFixer\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class OutputContext
{
    private ?OutputInterface $output;
    private int $terminalWidth;
    private int $filesCount;

    public function __construct(
        ?OutputInterface $output,
        int $terminalWidth,
        int $filesCount
    ) {
        $this->output = $output;
        $this->terminalWidth = $terminalWidth;
        $this->filesCount = $filesCount;
    }

    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    public function getTerminalWidth(): int
    {
        return $this->terminalWidth;
    }

    public function getFilesCount(): int
    {
        return $this->filesCount;
    }
}
