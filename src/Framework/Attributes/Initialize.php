<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * A public method of a state machine with this attribute is called once
 * before TestCase::runStateMachine() takes the steps of the state machine,
 * with generated values or values that earlier methods with this attribute
 * produced (#[FromPool]) as its arguments. The value that it returns is added
 * to the pool that is named with $produces.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Initialize
{
    /**
     * @var ?non-empty-string
     */
    private ?string $produces;

    /**
     * @param ?non-empty-string $produces
     */
    public function __construct(?string $produces = null)
    {
        $this->produces = $produces;
    }

    /**
     * @return ?non-empty-string
     */
    public function produces(): ?string
    {
        return $this->produces;
    }
}
