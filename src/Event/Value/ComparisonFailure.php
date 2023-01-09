<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\ExpectationFailedException;
use Throwable;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ComparisonFailure
{
    private readonly Comparison $comparison;
    private readonly string $diff;

    public static function from(Throwable $t): ?self
    {
        if (!$t instanceof ExpectationFailedException) {
            return null;
        }

        if (!$t->getComparisonFailure()) {
            return null;
        }

        return new self(
            new Comparison(
                $t->getComparisonFailure()->getExpectedAsString(),
                $t->getComparisonFailure()->getActualAsString(),
            ),
            $t->getComparisonFailure()->getDiff()
        );
    }

    private function __construct(Comparison $comparison, string $diff)
    {
        $this->comparison = $comparison;
        $this->diff       = $diff;
    }

    public function comparison(): Comparison
    {
        return $this->comparison;
    }

    public function diff(): string
    {
        return $this->diff;
    }
}
