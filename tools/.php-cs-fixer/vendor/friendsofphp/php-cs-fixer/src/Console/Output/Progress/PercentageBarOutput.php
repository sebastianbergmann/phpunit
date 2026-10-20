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

namespace PhpCsFixer\Console\Output\Progress;

use PhpCsFixer\Console\Output\OutputContext;
use PhpCsFixer\Runner\Event\FileProcessed;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Output writer to show the progress of a FixCommand using progress bar (percentage).
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PercentageBarOutput implements ProgressOutputInterface
{
    /** @readonly */
    private OutputContext $context;

    private ProgressBar $progressBar;

    public function __construct(OutputContext $context)
    {
        $this->context = $context;

        $this->progressBar = new ProgressBar($context->getOutput(), $this->context->getFilesCount());
        $this->progressBar->setBarCharacter('▓'); // dark shade character \u2593
        $this->progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
        $this->progressBar->setProgressCharacter('');
        $this->progressBar->setFormat('normal');

        $this->progressBar->start();
    }

    /**
     * This class is not intended to be serialized,
     * and cannot be deserialized (see __wakeup method).
     */
    public function __serialize(): array
    {
        throw new \BadMethodCallException('Cannot serialize '.self::class);
    }

    /**
     * Disable the deserialization of the class to prevent attacker executing
     * code by leveraging the __destruct method.
     *
     * @param array<string, mixed> $data
     *
     * @see https://owasp.org/www-community/vulnerabilities/PHP_Object_Injection
     */
    public function __unserialize(array $data): void
    {
        throw new \BadMethodCallException('Cannot unserialize '.self::class);
    }

    public function onFixerFileProcessed(FileProcessed $event): void
    {
        $this->progressBar->advance(1);

        if ($this->progressBar->getProgress() === $this->progressBar->getMaxSteps()) {
            $this->context->getOutput()->write("\n\n");
        }
    }

    public function printLegend(): void {}
}
