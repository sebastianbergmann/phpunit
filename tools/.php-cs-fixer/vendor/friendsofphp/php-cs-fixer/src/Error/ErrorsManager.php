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
 * Manager of errors that occur during fixing.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ErrorsManager
{
    /**
     * @var list<Error>
     */
    private array $errors = [];

    /**
     * Returns errors reported during linting before fixing.
     *
     * @return list<Error>
     */
    public function getInvalidErrors(): array
    {
        return array_values(array_filter($this->errors, static fn (Error $error): bool => Error::TYPE_INVALID === $error->getType()));
    }

    /**
     * Returns errors reported during fixing.
     *
     * @return list<Error>
     */
    public function getExceptionErrors(): array
    {
        return array_values(array_filter($this->errors, static fn (Error $error): bool => Error::TYPE_EXCEPTION === $error->getType()));
    }

    /**
     * Returns errors reported during linting after fixing.
     *
     * @return list<Error>
     */
    public function getLintErrors(): array
    {
        return array_values(array_filter($this->errors, static fn (Error $error): bool => Error::TYPE_LINT === $error->getType()));
    }

    /**
     * @return list<Error>
     */
    public function getAllErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns errors reported for specified path.
     *
     * @return list<Error>
     */
    public function forPath(string $path): array
    {
        return array_values(array_filter($this->errors, static fn (Error $error): bool => $path === $error->getFilePath()));
    }

    /**
     * Returns true if no errors were reported.
     */
    public function isEmpty(): bool
    {
        return [] === $this->errors;
    }

    public function report(Error $error): void
    {
        $this->errors[] = $error;
    }
}
