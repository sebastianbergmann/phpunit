<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use Generator;
use ReflectionClass;
use Throwable;

abstract class DataProvidedTestSuite extends TestSuite
{
    protected $theClass;
    protected $method;
    public function __construct(ReflectionClass $theClass, string $method)
    {
        parent::__construct($theClass, $theClass->getName().'::'.$method, true);
        $this->theClass = $theClass;
        $this->method = $method;
    }
    /**
     * @var string[]
     */
    private $dependencies = [];

    /**
     * @param string[] $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;

        foreach ($this->tests as $test) {
            $test->setDependencies($dependencies);
        }
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function hasDependencies(): bool
    {
        return \count($this->dependencies) > 0;
    }

    abstract protected function yieldData(): iterable;

    /**
     * @return Generator|Test[]
     */
    protected function yieldTests(): Generator
    {
        yield from parent::yieldTests();
        try {
            foreach ($this->yieldData() as $key => $set) {
                $name = is_int($key) ? "#$key" : $key;
                if(!is_array($set)) {
                    yield self::warning("{$this->name} set $name is invalid.");
                    continue;
                }

                try {
                    $test = $this->theClass->newInstanceArgs([
                        $this->method,
                        $set,
                        $name
                    ]);
                    $test->setDependencies($this->dependencies);
                    yield $test;
                } catch (Throwable $e) {
                    yield self::warning("Test creation failed for {$this->name} with set $name");
                }
            }
        } catch (SkippedTestError $e) {
            yield self::warning("Test for {$this->name} skipped by data provider.");
        } catch (Throwable $e) {
            yield self::warning("data provider for {$this->name} failed");
        }
    }

    public function count($preferCache = false): int
    {
        return 1;
    }
}