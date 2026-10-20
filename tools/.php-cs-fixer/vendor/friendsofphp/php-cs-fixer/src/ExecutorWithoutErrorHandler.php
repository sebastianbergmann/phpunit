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

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ExecutorWithoutErrorHandler
{
    private function __construct() {}

    /**
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     *
     * @throws ExecutorWithoutErrorHandlerException
     */
    public static function execute(callable $callback)
    {
        /** @var ?string */
        $error = null;

        set_error_handler(static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine) use (&$error): bool {
            $error = $errorString;

            return true;
        });

        try {
            $result = $callback();
        } finally {
            restore_error_handler();
        }

        if (null !== $error) {
            throw new ExecutorWithoutErrorHandlerException($error);
        }

        return $result;
    }
}
