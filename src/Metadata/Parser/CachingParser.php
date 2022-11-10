<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use PHPUnit\Metadata\MetadataCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CachingParser implements Parser
{
    private readonly Parser $reader;
    private array $classCache          = [];
    private array $methodCache         = [];
    private array $classAndMethodCache = [];

    public function __construct(Parser $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClass(string $className): MetadataCollection
    {
        if (isset($this->classCache[$className])) {
            return $this->classCache[$className];
        }

        $this->classCache[$className] = $this->reader->forClass($className);

        return $this->classCache[$className];
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $key = $className . '::' . $methodName;

        if (isset($this->methodCache[$key])) {
            return $this->methodCache[$key];
        }

        $this->methodCache[$key] = $this->reader->forMethod($className, $methodName);

        return $this->methodCache[$key];
    }

    /**
     * @psalm-param class-string $className
     */
    public function forClassAndMethod(string $className, string $methodName): MetadataCollection
    {
        $key = $className . '::' . $methodName;

        if (isset($this->classAndMethodCache[$key])) {
            return $this->classAndMethodCache[$key];
        }

        $this->classAndMethodCache[$key] = $this->forClass($className)->mergeWith(
            $this->forMethod($className, $methodName)
        );

        return $this->classAndMethodCache[$key];
    }
}
