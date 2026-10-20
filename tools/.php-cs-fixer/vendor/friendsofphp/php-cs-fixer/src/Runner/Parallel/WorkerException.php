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
 * @author Greg Korba <gre@codito.dev>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class WorkerException extends \RuntimeException
{
    private string $originalTraceAsString;

    private function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
    }

    /**
     * @param array{
     *     class: class-string<\Throwable>,
     *     message: string,
     *     file: string,
     *     line: int,
     *     code: int,
     *     trace: string,
     *     ...
     * } $data
     */
    public static function fromRaw(array $data): self
    {
        $exception = new self(
            \sprintf('[%s] %s', $data['class'], $data['message']),
            $data['code'],
        );
        $exception->file = $data['file'];
        $exception->line = $data['line'];
        $exception->originalTraceAsString = \sprintf(
            '## %s(%d)%s%s',
            $data['file'],
            $data['line'],
            \PHP_EOL,
            $data['trace'],
        );

        return $exception;
    }

    public function getOriginalTraceAsString(): string
    {
        return $this->originalTraceAsString;
    }
}
