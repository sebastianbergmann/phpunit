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

class MockTrait implements MockBrick
{

    /**
     * @var string
     */
    private $classCode;

    /**
     * @var string
     */
    private $mockName;

    public function __construct(string $classCode, string $mockName)
    {
        $this->classCode = $classCode;
        $this->mockName = $mockName;
    }

    public function getClassCode(): string
    {
        return $this->classCode;
    }

    public function getMockName(): string
    {
        return $this->mockName;
    }

    public function bringIntoExistence(): string
    {
        if (!\class_exists($this->getMockName(), false)) {
            eval($this->getClassCode());
        }
        return $this->getMockName();
    }

}
