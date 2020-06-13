<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Util\Test as TestUtil;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DataProviderTestSuite extends TestSuite
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
        $this->dependencies  = $dependencies;
        $this->requiredTests = \PHPUnit\Util\Test::trimDependencyOptions($dependencies);

        foreach ($this->tests as $test) {
            if (!$test instanceof TestCase) {
                continue;
            }

            $test->setDependencies($dependencies);
        }
    }

    /**
     * @return string[]
     */
    public function provides(): array
    {
        return [$this->getName()];
    }

    /**
     * @return string[]
     */
    public function requires(): array
    {
        // A DataProviderTestSuite does not have to traverse its child tests
        // as these are inherited and cannot reference dataProvider rows directly
        return $this->requiredTests ?? [];
    }

    /**
     * Returns the size of the each test created using the data provider(s).
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function getSize(): int
    {
        [$className, $methodName] = \explode('::', $this->getName());

        return TestUtil::getSize($className, $methodName);
    }
}
