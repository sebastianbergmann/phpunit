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

use function explode;
use function sprintf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Filter;
use SebastianBergmann\Invoker\TimeoutException;
use Throwable;

/**
 * How an example falsified a property.
 *
 * Two examples falsify a property in the same way when the throwables that
 * ended them have the same class and were raised at the same location, or
 * when both exceeded the time limit. The location is the one PHPUnit reports
 * for a throwable: the first frame of its stack trace that is not in PHPUnit's
 * own code, or else the file and line of the throwable itself. The file and
 * line of the throwable alone would not do, because every failed assertion is
 * raised in PHPUnit's own code.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Falsification
{
    private string $signature;
    private bool $timeout;

    /**
     * Returns null when the example did not falsify the property, that is,
     * when it neither failed nor errored nor exceeded its time limit.
     */
    public static function of(TestCase $example): ?self
    {
        $throwable = $example->throwableThatEndedTheTest();

        if ($throwable instanceof TimeoutException) {
            return new self('timeout', true);
        }

        $status = $example->status();

        if (!$status->isFailure() && !$status->isError()) {
            return null;
        }

        if ($throwable === null) {
            // @codeCoverageIgnoreStart
            return new self(sprintf('%s: %s', $status->asString(), $status->message()), false);
            // @codeCoverageIgnoreEnd
        }

        return new self(
            sprintf(
                '%s at %s',
                $throwable::class,
                self::locationOf($throwable),
            ),
            false,
        );
    }

    private function __construct(string $signature, bool $timeout)
    {
        $this->signature = $signature;
        $this->timeout   = $timeout;
    }

    /**
     * Whether the example was aborted because it exceeded its time limit.
     */
    public function isTimeout(): bool
    {
        return $this->timeout;
    }

    public function isSameAs(self $other): bool
    {
        return $this->signature === $other->signature;
    }

    private static function locationOf(Throwable $throwable): string
    {
        $frame = explode("\n", Filter::stackTraceFromThrowableAsString($throwable, false))[0];

        if ($frame !== '') {
            return $frame;
        }

        // every frame is in PHPUnit's own code, for instance for an unmet
        // expectation for a test double, which cannot happen in PHPUnit's
        // own test suite because it does not exclude PHPUnit's own code
        // @codeCoverageIgnoreStart
        return $throwable->getFile() . ':' . $throwable->getLine();
        // @codeCoverageIgnoreEnd
    }
}
