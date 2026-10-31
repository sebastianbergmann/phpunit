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
use function explode;
use function getcwd;
use function realpath;
use function rtrim;
use function sprintf;
use function str_starts_with;
use function strcmp;
use function strlen;
use function strrpos;
use function substr;
use Error;
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
 * When unbounded recursion reaches the limit of PHP's call stack, the error is
 * raised by whichever call of the recursion exceeds the limit, which depends
 * on how deep the call stack was when the recursion started, and on how much
 * of it the calls use, which differs between versions of PHP. So that the same
 * recursion has the same location, the location of this error is the frame of
 * the recursion that comes first, by file and then by line, among the frames
 * that occur more than once in its stack trace.
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
    /**
     * The message of the error that PHP raises when the call stack limit is
     * reached starts with this.
     */
    private const string CALL_STACK_LIMIT = 'Maximum call stack size of ';
    private FailureSignature $signature;

    /**
     * @var ?class-string
     */
    private ?string $className;

    /**
     * @var ?non-empty-string
     */
    private ?string $location;
    private string $message;

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
            return new self(FailureSignature::forTimeout(), null, null, $throwable->getMessage());
        }

        $status = $example->status();

        if ($throwable === null) {
            // @codeCoverageIgnoreStart
            return new self(FailureSignature::forDescription(sprintf('%s: %s', $status->asString(), $status->message())), null, null, $status->message());
            // @codeCoverageIgnoreEnd
        }

        return self::ofThrowable($throwable);
    }

    /**
     * How a throwable falsified the property: the throwable that ended an
     * example, or the one that was raised while its arguments were generated.
     */
    public static function ofThrowable(Throwable $throwable): self
    {
        $location = self::locationOf($throwable);

        return new self(
            FailureSignature::forThrowable($throwable::class, $location),
            $throwable::class,
            $location,
            $throwable->getMessage(),
        );
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

    /**
     * @param ?class-string     $className
     * @param ?non-empty-string $location
     */
    private function __construct(FailureSignature $signature, ?string $className, ?string $location, string $message)
    {
        $this->signature = $signature;
        $this->className = $className;
        $this->location  = $location;
        $this->message   = $message;
    }

    public function signature(): FailureSignature
    {
        return $this->signature;
    }

    /**
     * The class of the throwable that falsified the property, null when no
     * throwable did, as for an example that exceeded its time limit.
     *
     * @return ?class-string
     */
    public function className(): ?string
    {
        return $this->className;
    }

    /**
     * Where the throwable that falsified the property was raised, null when
     * no throwable did.
     *
     * @return ?non-empty-string
     */
    public function location(): ?string
    {
        return $this->location;
    }

    /**
     * The message of the throwable that falsified the property, or of the
     * status of the example when no throwable did.
     */
    public function message(): string
    {
        return $this->message;
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
        if ($throwable::class === Error::class && str_starts_with($throwable->getMessage(), self::CALL_STACK_LIMIT)) {
            $frame = self::firstRepeatedFrame($throwable);

            if ($frame !== null) {
                return $frame;
            }
        }

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
     * Returns the frame that comes first, by file and then by line, among the
     * frames of the stack trace of the throwable that occur more than once, or
     * null when no frame does.
     *
     * @return ?non-empty-string
     */
    private static function firstRepeatedFrame(Throwable $throwable): ?string
    {
        $occurrences = [];

        foreach (explode("\n", Filter::stackTraceFromThrowableAsString($throwable, false)) as $frame) {
            if ($frame !== '') {
                $occurrences[$frame] = ($occurrences[$frame] ?? 0) + 1;
            }
        }

        $first = null;

        foreach ($occurrences as $frame => $count) {
            if ($count < 2) {
                continue;
            }

            $frame = self::relativeToWorkingDirectory($frame);

            if ($first === null || self::comesBefore($frame, $first)) {
                $first = $frame;
            }
        }

        return $first;
    }

    /**
     * Whether a frame, rendered as file:line, comes before the other one, by
     * file and then by line.
     */
    private static function comesBefore(string $frame, string $other): bool
    {
        [$file, $line]           = self::fileAndLine($frame);
        [$otherFile, $otherLine] = self::fileAndLine($other);

        $order = strcmp($file, $otherFile);

        return $order < 0 || ($order === 0 && $line < $otherLine);
    }

    /**
     * @return array{0: string, 1: int}
     */
    private static function fileAndLine(string $frame): array
    {
        $separator = strrpos($frame, ':');

        if ($separator === false) {
            // @codeCoverageIgnoreStart
            return [$frame, 0];
            // @codeCoverageIgnoreEnd
        }

        return [substr($frame, 0, $separator), (int) substr($frame, $separator + 1)];
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
