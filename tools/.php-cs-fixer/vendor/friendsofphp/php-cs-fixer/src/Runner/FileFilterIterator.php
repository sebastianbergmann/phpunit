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

namespace PhpCsFixer\Runner;

use PhpCsFixer\Cache\CacheManagerInterface;
use PhpCsFixer\FileReader;
use PhpCsFixer\Runner\Event\FileProcessed;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 *
 * @extends \FilterIterator<mixed, \SplFileInfo, \Iterator<mixed, \SplFileInfo>>
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FileFilterIterator extends \FilterIterator
{
    private ?EventDispatcherInterface $eventDispatcher;

    private CacheManagerInterface $cacheManager;

    /**
     * @var array<string, bool>
     */
    private array $visitedElements = [];

    /**
     * @param iterable<\SplFileInfo> $iterator
     */
    public function __construct(
        iterable $iterator,
        ?EventDispatcherInterface $eventDispatcher,
        CacheManagerInterface $cacheManager
    ) {
        if (!$iterator instanceof \Iterator) {
            $iterator = new \IteratorIterator(
                $iterator instanceof \Traversable ? $iterator : new \ArrayIterator($iterator),
            );
        }

        parent::__construct($iterator);

        $this->eventDispatcher = $eventDispatcher;
        $this->cacheManager = $cacheManager;
    }

    public function accept(): bool
    {
        $file = $this->current();
        if (!$file instanceof \SplFileInfo) {
            throw new \RuntimeException(
                \sprintf(
                    'Expected instance of "\SplFileInfo", got "%s".',
                    get_debug_type($file),
                ),
            );
        }

        $path = $file->isLink() ? $file->getPathname() : $file->getRealPath();

        if (isset($this->visitedElements[$path])) {
            return false;
        }

        $this->visitedElements[$path] = true;

        if (!$file->isFile() || $file->isLink()) {
            return false;
        }

        $content = FileReader::createSingleton()->read($path);

        // mark as skipped:
        if (
            // empty file
            '' === $content
            // file that does not need fixing due to cache
            || !$this->cacheManager->needFixing($file->getPathname(), $content)
        ) {
            $this->dispatchEvent(FileProcessed::NAME, new FileProcessed(FileProcessed::STATUS_SKIPPED));

            return false;
        }

        return true;
    }

    private function dispatchEvent(string $name, Event $event): void
    {
        if (null === $this->eventDispatcher) {
            return;
        }

        $this->eventDispatcher->dispatch($event, $name);
    }
}
