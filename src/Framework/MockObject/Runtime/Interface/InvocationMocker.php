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

interface InvocationMocker extends InvocationStubber
{
    /**
     * @return $this
     */
    public function with(mixed ...$arguments): self;

    /**
     * @return $this
     */
    public function withParameterSetsInOrder(mixed ...$arguments): self;

    /**
     * @return $this
     */
    public function withParameterSetsInAnyOrder(mixed ...$arguments): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function withAnyParameters(): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param non-empty-string $id
     *
     * @return $this
     */
    public function id(string $id): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param non-empty-string $id
     *
     * @return $this
     */
    public function after(string $id): self;
}
