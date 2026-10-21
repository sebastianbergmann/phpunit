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
 * A public method of a state machine with this attribute is a rule: a step
 * that TestCase::runStateMachine() can take. Its parameters receive generated
 * values, or values that earlier steps produced (#[FromPool]). The rule is
 * only taken while the public method of the state machine that is named as
 * its precondition returns true. The value that the rule returns is added to
 * the pool that is named with $produces.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Rule
{
    /**
     * @var ?non-empty-string
     */
    private ?string $precondition;

    /**
     * @var ?non-empty-string
     */
    private ?string $produces;

    /**
     * @param ?non-empty-string $precondition
     * @param ?non-empty-string $produces
     */
    public function __construct(?string $precondition = null, ?string $produces = null)
    {
        $this->precondition = $precondition;
        $this->produces     = $produces;
    }

    /**
     * @return ?non-empty-string
     */
    public function precondition(): ?string
    {
        return $this->precondition;
    }

    /**
     * @return ?non-empty-string
     */
    public function produces(): ?string
    {
        return $this->produces;
    }
}
