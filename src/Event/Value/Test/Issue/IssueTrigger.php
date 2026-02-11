<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code\IssueTrigger;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class IssueTrigger
{
    private ?Code $callee;
    private ?Code $caller;

    protected function __construct(?Code $callee, ?Code $caller)
    {
        $this->callee = $callee;
        $this->caller = $caller;
    }

    /**
     * An issue is triggered in test code.
     */
    abstract public function isTest(): bool;

    /**
     * An issue is triggered in first-party code or in test code.
     */
    abstract public function isSelf(): bool;

    /**
     * First-party code triggers an issue in third-party code.
     */
    abstract public function isDirect(): bool;

    /**
     * Third-party code triggers an issue.
     */
    abstract public function isIndirect(): bool;

    abstract public function isUnknown(): bool;

    abstract public function asString(): string;

    protected function callee(): ?Code
    {
        return $this->callee;
    }

    protected function caller(): ?Code
    {
        return $this->caller;
    }
}
