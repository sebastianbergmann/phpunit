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

use function call_user_func;
use function class_exists;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockClass implements MockType
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
        $this->classCode           = $classCode;
        $this->mockName            = $mockName;
        $this->configurableMethods = $configurableMethods;
    }

    public function generate(): string
    {
        if (!class_exists($this->mockName, false)) {
            eval($this->classCode);

            call_user_func(
                [
                    $this->mockName,
                    '__phpunit_initConfigurableMethods',
                ],
                ...$this->configurableMethods
            );
        }

        return $this->mockName;
    }

    public function getClassCode(): string
    {
        return $this->classCode;
    }
}
