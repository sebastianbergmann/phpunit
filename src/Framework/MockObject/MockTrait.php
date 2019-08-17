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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockTrait implements MockType
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
        $this->mockName  = $mockName;
    }

    public function generate(): string
    {
        if (!\class_exists($this->mockName, false)) {
            eval($this->classCode);
        }

        return $this->mockName;
    }

    public function getClassCode(): string
    {
        return $this->classCode;
    }
}
