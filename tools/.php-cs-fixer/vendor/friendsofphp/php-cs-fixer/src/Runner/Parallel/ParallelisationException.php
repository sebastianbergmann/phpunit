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

namespace PhpCsFixer\Runner\Parallel;

/**
 * Common exception for all the errors related to parallelisation.
 *
 * @author Greg Korba <greg@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ParallelisationException extends \RuntimeException
{
    public static function forUnknownIdentifier(ProcessIdentifier $identifier): self
    {
        return new self('Unknown process identifier: '.$identifier->toString());
    }

    public static function forUnexpectedWorkerExit(?int $exitCode, ?int $termSignal, string $output): self
    {
        $output = trim($output);
        if (\strlen($output) > 1_000) {
            $output = substr($output, 0, 1_000).'...';
        }

        return new self(\sprintf(
            'Worker process exited unexpectedly (exit code: %s, signal: %s), its files were not analysed.%s',
            $exitCode ?? 'n/a',
            $termSignal ?? 'n/a',
            '' !== $output ? "\nWorker output:\n".$output : '',
        ));
    }
}
