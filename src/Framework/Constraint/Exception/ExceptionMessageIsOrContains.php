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

use function is_string;
use function sprintf;
use function str_contains;
use PHPUnit\Util\Exporter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExceptionMessageIsOrContains extends Constraint
{
    private readonly string $expectedMessage;

    public function __construct(string $expectedMessage)
    {
        $this->expectedMessage = $expectedMessage;
    }

    public function toString(): string
    {
        if ($this->expectedMessage === '') {
            return 'exception message is empty';
        }

        return 'exception message contains ' . Exporter::export($this->expectedMessage);
    }

    protected function matches(mixed $other): bool
    {
        if ($this->expectedMessage === '') {
            return $other === '';
        }

        if (!is_string($other)) {
            return false;
        }

        return str_contains($other, $this->expectedMessage);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        if (is_string($other)) {
            $message = $other;
        } else {
            $message = '';
        }

        if ($this->expectedMessage === '') {
            return sprintf(
                "exception message is empty but is '%s'",
                $message,
            );
        }

        return sprintf(
            "exception message '%s' contains '%s'",
            $message,
            $this->expectedMessage,
        );
    }
}
