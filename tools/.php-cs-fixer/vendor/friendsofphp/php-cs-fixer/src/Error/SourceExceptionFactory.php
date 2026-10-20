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

namespace PhpCsFixer\Error;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SourceExceptionFactory
{
    /**
     * @param array{class: class-string<\Throwable>, message: string, code: int, file: string, line: int} $error
     */
    public static function fromArray(array $error): \Throwable
    {
        $exceptionClass = $error['class'];

        try {
            $exception = new $exceptionClass($error['message'], $error['code']);

            if (
                $exception->getMessage() !== $error['message']
                || $exception->getCode() !== $error['code']
            ) {
                throw new \RuntimeException('Failed to create exception from array. Message and code are not the same.');
            }
        } catch (\Throwable $e) {
            $exception = new \RuntimeException(
                \sprintf('[%s] %s', $exceptionClass, $error['message']),
                $error['code'],
            );
        }

        try {
            $exceptionReflection = new \ReflectionClass($exception);
            foreach (['file', 'line'] as $property) {
                $propertyReflection = $exceptionReflection->getProperty($property);

                if (\PHP_VERSION_ID < 8_01_00) {
                    $propertyReflection->setAccessible(true); // @phpstan-ignore method.deprecated
                }

                $propertyReflection->setValue($exception, $error[$property]);

                if (\PHP_VERSION_ID < 8_01_00) {
                    $propertyReflection->setAccessible(false); // @phpstan-ignore method.deprecated
                }
            }
        } catch (\Throwable $reflectionException) {
            // Ignore if we were not able to set file/line properties. In most cases it should be fine,
            // we just need to make sure nothing is broken when we recreate errors from raw data passed from worker.
        }

        return $exception;
    }
}
