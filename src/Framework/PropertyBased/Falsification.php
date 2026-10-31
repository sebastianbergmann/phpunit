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

use const DIRECTORY_SEPARATOR;
use function getcwd;
use function realpath;
use function rtrim;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Filter;
use SebastianBergmann\ChoiceSequence\FailureSignature;
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
 * The path of a file below the working directory is relative to it, so that
 * the location does not depend on where a project is checked out.
 *
 * Whether two examples falsify the property in the same way is told by the
 * failure signature of sebastian/choice-sequence, so that failures are
 * compared in the same way wherever that library is used.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Falsification
{
    private FailureSignature $signature;

    /**
     * Returns null when the example did not falsify the property, that is,
     * when it neither failed nor errored nor exceeded its time limit.
     */
    public static function of(TestCase $example): ?self
    {
        if (!self::exampleFalsifies($example)) {
            return null;
        }

        $throwable = $example->throwableThatEndedTheTest();

        if ($throwable instanceof TimeoutException) {
            return new self(FailureSignature::forTimeout());
        }

        $status = $example->status();

        if ($throwable === null) {
            // @codeCoverageIgnoreStart
            return new self(FailureSignature::forDescription(sprintf('%s: %s', $status->asString(), $status->message())));
            // @codeCoverageIgnoreEnd
        }

        return new self(FailureSignature::forThrowable($throwable::class, self::locationOf($throwable)));
    }

    /**
     * Whether the example falsified the property, without describing how,
     * which requires filtering the stack trace of the throwable that ended
     * the example.
     */
    public static function exampleFalsifies(TestCase $example): bool
    {
        if ($example->throwableThatEndedTheTest() instanceof TimeoutException) {
            return true;
        }

        return $example->status()->isFailure() || $example->status()->isError();
    }

    private function __construct(FailureSignature $signature)
    {
        $this->signature = $signature;
    }

    /**
     * Whether the example was aborted because it exceeded its time limit.
     */
    public function isTimeout(): bool
    {
        return $this->signature->isTimeout();
    }

    public function isSameAs(self $other): bool
    {
        return $this->signature->isSameAs($other->signature);
    }

    /**
     * Describes how the example falsified the property, for instance
     * "RuntimeException at /path/to/FooTest.php:12" or "exceeding the time
     * limit".
     *
     * @return non-empty-string
     */
    public function description(): string
    {
        return $this->signature->description();
    }

    /**
     * @return non-empty-string
     */
    /**
     * @return non-empty-string
     */
    private static function locationOf(Throwable $throwable): string
    {
        $frame = Filter::firstFrameFromThrowableAsString($throwable, false);

        if ($frame === null) {
            // every frame is in PHPUnit's own code, for instance for an unmet
            // expectation for a test double, which cannot happen in PHPUnit's
            // own test suite because it does not exclude PHPUnit's own code
            // @codeCoverageIgnoreStart
            $frame = $throwable->getFile() . ':' . $throwable->getLine();
            // @codeCoverageIgnoreEnd
        }

        return self::relativeToWorkingDirectory($frame);
    }

    /**
     * The working directory is the one in which the example was started,
     * because PHPUnit restores it after the example, should the example
     * change it.
     *
     * @param non-empty-string $location
     *
     * @return non-empty-string
     */
    private static function relativeToWorkingDirectory(string $location): string
    {
        $workingDirectory = getcwd();

        if ($workingDirectory === false) {
            // @codeCoverageIgnoreStart
            return $location;
            // @codeCoverageIgnoreEnd
        }

        $realpath = realpath($workingDirectory);

        if ($realpath !== false) {
            $workingDirectory = $realpath;
        }

        $prefix = rtrim($workingDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($location, $prefix)) {
            return $location;
        }

        $relative = substr($location, strlen($prefix));

        if ($relative === '') {
            // @codeCoverageIgnoreStart
            return $location;
            // @codeCoverageIgnoreEnd
        }

        return $relative;
    }
}
