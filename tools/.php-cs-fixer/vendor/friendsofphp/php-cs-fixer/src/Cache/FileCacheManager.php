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

namespace PhpCsFixer\Cache;

use PhpCsFixer\Hasher;

/**
 * Class supports caching information about state of fixing files.
 *
 * Cache is supported only for phar version and version installed via composer.
 *
 * File will be processed by PHP CS Fixer only if any of the following conditions is fulfilled:
 *  - cache is corrupt
 *  - fixer version changed
 *  - rules changed
 *  - file is new
 *  - file changed
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FileCacheManager implements CacheManagerInterface
{
    public const WRITE_FREQUENCY = 10;

    private FileHandlerInterface $handler;

    private SignatureInterface $signature;

    private bool $isDryRun;

    private DirectoryInterface $cacheDirectory;

    private int $writeCounter = 0;

    private bool $signatureWasUpdated = false;

    private CacheInterface $cache;

    public function __construct(
        FileHandlerInterface $handler,
        SignatureInterface $signature,
        bool $isDryRun = false,
        ?DirectoryInterface $cacheDirectory = null
    ) {
        $this->handler = $handler;
        $this->signature = $signature;
        $this->isDryRun = $isDryRun;
        $this->cacheDirectory = $cacheDirectory ?? new Directory('');

        $this->readCache();
    }

    public function __destruct()
    {
        if (true === $this->signatureWasUpdated || 0 !== $this->writeCounter) {
            $this->writeCache();
        }
    }

    /**
     * This class is not intended to be serialized,
     * and cannot be deserialized (see __wakeup method).
     */
    public function __serialize(): array
    {
        throw new \BadMethodCallException('Cannot serialize '.__CLASS__);
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
        throw new \BadMethodCallException('Cannot unserialize '.__CLASS__);
    }

    public function needFixing(string $file, string $fileContent): bool
    {
        $file = $this->cacheDirectory->getRelativePathTo($file);

        return !$this->cache->has($file) || $this->cache->get($file) !== $this->calcHash($fileContent);
    }

    public function setFile(string $file, string $fileContent): void
    {
        $this->setFileHash($file, $this->calcHash($fileContent));
    }

    public function setFileHash(string $file, string $hash): void
    {
        $file = $this->cacheDirectory->getRelativePathTo($file);

        if ($this->isDryRun && $this->cache->has($file) && $this->cache->get($file) !== $hash) {
            $this->cache->clear($file);
        } else {
            $this->cache->set($file, $hash);
        }

        if (self::WRITE_FREQUENCY === ++$this->writeCounter) {
            $this->writeCounter = 0;
            $this->writeCache();
        }
    }

    private function readCache(): void
    {
        $cache = $this->handler->read();

        if (null === $cache || !$this->signature->equals($cache->getSignature())) {
            $cache = new Cache($this->signature);
            $this->signatureWasUpdated = true;
        }

        $this->cache = $cache;
    }

    private function writeCache(): void
    {
        $this->handler->write($this->cache);
    }

    private function calcHash(string $content): string
    {
        return Hasher::calculate($content);
    }
}
