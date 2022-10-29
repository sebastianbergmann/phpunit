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

use function function_exists;
use function json_decode;
use function json_last_error;
use function json_validate;
use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsJson extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is valid JSON';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if ($other === '') {
            return false;
        }

        if (function_exists('json_validate')) {
            return json_validate($other);
        }

        json_decode($other);

        if (json_last_error()) {
            return false;
        }

        return true;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        if ($other === '') {
            return 'an empty string is valid JSON';
        }

        if (function_exists('json_validate')) {
            json_validate($other);
        } else {
            json_decode($other);
        }

        $error = (string) JsonMatchesErrorMessageProvider::determineJsonError(
            json_last_error()
        );

        return sprintf(
            '%s is valid JSON (%s)',
            $this->exporter()->shortenedExport($other),
            $error
        );
    }
}
