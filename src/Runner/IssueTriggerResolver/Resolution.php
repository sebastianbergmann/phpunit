<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\IssueTriggerResolver;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Resolution
{
    private ?string $callee;
    private ?string $caller;

    public function __construct(?string $callee, ?string $caller)
    {
        $this->callee = $callee;
        $this->caller = $caller;
    }

    /**
     * @phpstan-assert-if-true !null $this->callee
     */
    public function hasCallee(): bool
    {
        return $this->callee !== null;
    }

    public function callee(): ?string
    {
        return $this->callee;
    }

    /**
     * @phpstan-assert-if-true !null $this->caller
     */
    public function hasCaller(): bool
    {
        return $this->caller !== null;
    }

    public function caller(): ?string
    {
        return $this->caller;
    }
}
