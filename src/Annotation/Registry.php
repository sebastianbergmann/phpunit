<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Annotation;

use PHPUnit\Util\Exception;

/**
 * Yes, this is a singleton registry. The reason why this can actually be a singleton
 * without having kittens die is that reflection information (and therefore docblock
 * information) is static within a single process.
 *
 * @internal This class is part of PHPUnit internals, an not intended
 *           for downstream usage
 *
 * @TODO test me (srsly!)
 */
final class Registry
{
    /** @var self|null */
    private static $instance;

    /** @var array<string, DocBlock> indexed by class name */
    private $classDocBlocks = [];

    /** @var array<string, array<string, DocBlock>> indexed by class name and method name */
    private $methodDocBlocks = [];

    private function __construct()
    {
    }

    public static function singleton() : self
    {
        return self::$instance
            ?? self::$instance = new self();
    }

    /**
     * @throws Exception
     * @psalm-param class-string $class
     */
    public function forClassName(string $class) : DocBlock
    {
        if (\array_key_exists($class, $this->classDocBlocks)) {
            return $this->classDocBlocks[$class];
        }

        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return $this->classDocBlocks[$class] = DocBlock::ofClass($reflection);
    }

    /**
     * @throws Exception
     * @psalm-param class-string $className
     */
    public function forMethod(string $class, string $method) : DocBlock
    {
        if (isset($this->methodDocBlocks[$class][$method])) {
            return $this->methodDocBlocks[$class][$method];
        }

        try {
            $reflection = new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return $this->methodDocBlocks[$class][$method] = DocBlock::ofMethod($reflection);
    }
}
