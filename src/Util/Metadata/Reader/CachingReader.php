<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CachingReader implements Reader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        if (isset($this->cache[$className])) {
            return $this->cache[$className];
        }

        $this->cache[$className] = $this->reader->forClass($className);

        return $this->cache[$className];
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $key = $className . '::' . $methodName;

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $this->cache[$key] = $this->reader->forMethod($className, $methodName);

        return $this->cache[$key];
    }
}
