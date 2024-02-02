<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function sprintf;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExceptionCode extends Constraint
{
    private readonly int|string $expectedCode;

    public function __construct(int|string $expected)
    {
        $this->expectedCode = $expected;
    }

    public function toString(): string
    {
        return 'exception code is ' . $this->expectedCode;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        return (string) $other === (string) $this->expectedCode;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        $exporter = new Exporter;

        return sprintf(
            '%s is equal to expected exception code %s',
            $exporter->export($other),
            $exporter->export($this->expectedCode),
        );
    }
}
