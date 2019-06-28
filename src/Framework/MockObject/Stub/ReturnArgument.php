<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Invocation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnArgument implements Stub
{
    /**
     * @var int
     */
    private $argumentIndex;

    public function __construct($argumentIndex)
    {
        $this->argumentIndex = $argumentIndex;
    }

    public function invoke(Invocation $invocation)
    {
        if (isset($invocation->getParameters()[$this->argumentIndex])) {
            return $invocation->getParameters()[$this->argumentIndex];
        }
    }

    public function toString(): string
    {
        return \sprintf('return argument #%d', $this->argumentIndex);
    }
}
