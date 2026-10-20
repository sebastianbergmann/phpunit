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

use function sprintf;
use RuntimeException;

/**
 * Reports a property that was falsified by an example, but not when that
 * example was replayed, or not when its minimal counterexample was replayed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class FlakyPropertyException extends RuntimeException implements Exception
{
    /**
     * @param positive-int $numberOfReplays
     */
    public static function exampleNotFalsifyingAgain(int $numberOfReplays): self
    {
        return new self(
            sprintf(
                'The property is flaky: it was not falsified when the example that falsified it was replayed %d times, so its outcome does not depend on its arguments alone',
                $numberOfReplays,
            ),
        );
    }

    public static function minimalCounterexampleNotFalsifyingAgain(): self
    {
        return new self('The property is flaky: it was not falsified when its minimal counterexample was replayed, so its outcome does not depend on its arguments alone');
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }
}
