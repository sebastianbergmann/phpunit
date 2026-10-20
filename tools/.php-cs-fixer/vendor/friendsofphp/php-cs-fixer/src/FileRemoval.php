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
 * Handles files removal with possibility to remove them on shutdown.
 *
 * @author Adam Klvač <adam@klva.cz>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FileRemoval
{
    /**
     * List of observed files to be removed.
     *
     * @var array<string, true>
     */
    private array $files = [];

    public function __construct()
    {
        register_shutdown_function([$this, 'clean']);
    }

    public function __destruct()
    {
        $this->clean();
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

    /**
     * Adds a file to be removed.
     */
    public function observe(string $path): void
    {
        $this->files[$path] = true;
    }

    /**
     * Removes a file from shutdown removal.
     */
    public function delete(string $path): void
    {
        if (isset($this->files[$path])) {
            unset($this->files[$path]);
        }

        $this->unlink($path);
    }

    /**
     * Removes attached files.
     */
    public function clean(): void
    {
        foreach ($this->files as $file => $value) {
            $this->unlink($file);
        }

        $this->files = [];
    }

    private function unlink(string $path): void
    {
        @unlink($path);
    }
}
