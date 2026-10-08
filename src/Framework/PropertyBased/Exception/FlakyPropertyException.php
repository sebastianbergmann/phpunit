<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use RuntimeException;
use Throwable;

/**
 * Reports a property that was falsified by an example, but not when its
 * minimal counterexample was replayed, together with the failure that was
 * originally observed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FlakyPropertyException extends RuntimeException implements Exception
{
    public static function originallyFalsifiedBy(?Throwable $throwable): self
    {
        return new self(
            'The property is flaky: it was not falsified when its minimal counterexample was replayed, so its outcome does not depend on its arguments alone',
            0,
            $throwable,
        );
    }
}
