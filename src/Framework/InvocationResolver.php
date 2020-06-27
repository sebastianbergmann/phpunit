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

class InvocationResolver // TODO: rename?
{
    /**
     * @var bool
     */
    private $returnValueGeneration;

    public function __construct(bool $returnValueGeneration)
    {
        $this->returnValueGeneration = $returnValueGeneration;
    }

    /**
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function invocationDefaultResult(Invocation $invocation)
    {
        if (!$this->returnValueGeneration) {
            throw new InvocationNotExpectedException($invocation);
        }

        return $invocation->generateReturnValue();
    }
}
