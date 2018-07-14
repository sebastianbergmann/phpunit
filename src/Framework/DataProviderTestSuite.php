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

class DataProviderTestSuite extends TestSuite
{
    private $provider;
    private $theClass;
    private $method;
    public function __construct($provider, ReflectionClass $theClass, string $method)
    {
        parent::__construct($theClass, $theClass->getName().'::'.$method, true);
        $this->provider = $provider;
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

    /**
     * @return Generator|Test[]
     */
    protected function yieldTests(): Generator
    {
        yield from parent::yieldTests();
        try {
            $provider = $this->theClass->newInstanceArgs();
            foreach ($provider->{$this->provider}() as $name => $set) {
                if(!is_array($set)) {
                    yield self::warning("set $name is invalid.");
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
                } catch (\Throwable $e) {
                    yield self::warning("Test creation failed. $e");
                }
            }
        } catch (\Throwable $e) {
            yield self::warning("data provider failed. $e");
        }
    }

    public function count($preferCache = false): int
    {
        return 1;
    }
}
