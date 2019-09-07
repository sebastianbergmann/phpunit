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

class DataProviderTestSuite extends TestSuite
{
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
            if (!$test instanceof TestCase) {
                continue;
            }

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
}
