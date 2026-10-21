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
 * A public method of a state machine with this attribute is an invariant:
 * TestCase::runStateMachine() calls it after the methods with #[Initialize]
 * and after every step, or, with $atEnd, only after the methods with
 * #[Initialize] and after the last step, which is meant for invariants that
 * are expensive to check. An invariant does not hold when it fails an
 * assertion or raises an exception.
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Invariant
{
    private bool $atEnd;

    public function __construct(bool $atEnd = false)
    {
        $this->atEnd = $atEnd;
    }

    public function atEnd(): bool
    {
        return $this->atEnd;
    }
}
