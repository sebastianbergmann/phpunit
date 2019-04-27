<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\MockObject;

class MockClass implements MockBrick
{

    /**
     * @var string
     */
    private $classCode;

    /**
     * @var string
     */
    private $mockName;

    /**
     * @var ConfigurableMethod[]
     */
    private $configurableMethods;

    public function __construct(string $classCode, string $mockName, array $configurableMethods)
    {
        $this->classCode = $classCode;
        $this->mockName = $mockName;
        $this->configurableMethods = $configurableMethods;
    }

    public function getClassCode(): string
    {
        return $this->classCode;
    }

    private function getMockName(): string
    {
        return $this->mockName;
    }

    public function bringIntoExistence(): string
    {
        if (!\class_exists($this->getMockName(), false)) {
            eval($this->getClassCode());
            call_user_func(array($this->getMockName(), '__phpunit_initConfigurableMethods'), ...$this->configurableMethods);
        }
        return $this->getMockName();
    }

}
