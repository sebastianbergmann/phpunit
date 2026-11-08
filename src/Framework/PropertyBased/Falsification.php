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
use const PHP_OS_FAMILY;
use function array_slice;
use function array_values;
use function assert;
use function count;
use function dirname;
use function getcwd;
use function realpath;
use function rtrim;
use function sprintf;
use function str_starts_with;
use function strcmp;
use function strlen;
use function strncasecmp;
use function strrpos;
use function substr;
use Error;
use PHPUnit\Framework\MockObject\StubInternal;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\Util\Filter;
use ReflectionClass;
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
 * When unbounded recursion reaches the limit of PHP's call stack, or the
 * limit of the nesting level of function calls of Xdebug, the error is raised
 * by whichever call of the recursion exceeds the limit, which depends on how
 * deep the call stack was when the recursion started, and on how much of it
 * the calls use, which differs between versions of PHP. So that the same
 * recursion has the same location, the location of this error is the frame of
 * the recursion that comes first, by file and then by line, among the frames
 * of the cycle that repeats at the top of its stack trace. The frames further
 * down, of a finite recursion that led to the unbounded one, for instance,
 * whose depth can depend on the example, are not considered. When no cycle
 * repeats at the top of the stack trace, the frames that occur more than once
 * in it are considered instead.
 *
 * The path of a file below the directory of the configuration file, or below
 * the working directory when there is no configuration file, is relative to
 * it, so that the location depends neither on where a project is checked out
 * nor on the working directory of the process.
 *
 * The name of an anonymous class contains the path of the file in which it is
 * declared and a counter that depends on the order in which anonymous classes
 * are compiled, and the name of the class of a test double ends in random
 * characters. So that the same failure has the same signature in every
 * process, the signature names the class that the class of such a throwable
 * extends, the first one that is neither anonymous nor a test double. Like
 * the location, the class of the throwable itself is kept as it is.
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

    /**
     * The message of the error that Xdebug raises when the nesting level of
     * function calls exceeds its limit starts with this.
     */
    private const string XDEBUG_NESTING_LIMIT = 'Xdebug has detected a possible infinite loop';

    /**
     * How often a cycle of frames must repeat at the top of a stack trace to
     * be the cycle of the recursion that reached a limit.
     */
    private const int REPETITIONS_OF_CYCLE = 3;

    /**
     * The longest cycle of frames that is looked for at the top of a stack
     * trace, which bounds the time spent on looking for one.
     */
    private const int MAXIMUM_LENGTH_OF_CYCLE = 1000;
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
        $status    = $example->status();

        if ($throwable === null) {
            // @codeCoverageIgnoreStart
            return new self(FailureSignature::forDescription(sprintf('%s: %s', $status->asString(), $status->message())), null, null, $status->message());
            // @codeCoverageIgnoreEnd
        }

        return self::ofThrowable($throwable);
    }

    /**
     * How a throwable falsified the property: the throwable that ended an
     * example, or the one that was raised while its arguments were generated,
     * which is a TimeoutException when the example exceeded its time limit.
     */
    public static function ofThrowable(Throwable $throwable): self
    {
        if ($throwable instanceof TimeoutException) {
            return new self(FailureSignature::forTimeout(), null, null, $throwable->getMessage());
        }

        $location = self::locationOf($throwable);

        return new self(
            FailureSignature::forThrowable(self::classForSignature($throwable), $location),
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
     * The class of the throwable, or, when it is anonymous or a test double,
     * the first class that it extends which is neither.
     *
     * @return class-string
     */
    private static function classForSignature(Throwable $throwable): string
    {
        $class = new ReflectionClass($throwable);

        while ($class->isAnonymous() || $class->implementsInterface(StubInternal::class)) {
            $parent = $class->getParentClass();

            // a throwable extends Exception or Error, which are neither
            // anonymous nor test doubles
            assert($parent !== false);

            $class = $parent;
        }

        return $class->getName();
    }

    /**
     * @return non-empty-string
     */
    private static function locationOf(Throwable $throwable): string
    {
        if (self::reachedRecursionLimit($throwable)) {
            $frame = self::firstFrameOfRecursion($throwable);

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

        return self::relativeToBaseDirectory($frame);
    }

    /**
     * Whether the throwable is the error that PHP raises when the limit of the
     * call stack is reached, or the one that Xdebug raises when the nesting
     * level of function calls exceeds its limit.
     */
    private static function reachedRecursionLimit(Throwable $throwable): bool
    {
        if ($throwable::class !== Error::class) {
            return false;
        }

        return str_starts_with($throwable->getMessage(), self::CALL_STACK_LIMIT) ||
               str_starts_with($throwable->getMessage(), self::XDEBUG_NESTING_LIMIT);
    }

    /**
     * Returns the frame that comes first, by file and then by line, among the
     * frames of the cycle that repeats at the top of the stack trace of the
     * throwable, or, when there is no such cycle, among the frames of its
     * stack trace that occur more than once. Returns null when no frame does.
     *
     * @return ?non-empty-string
     */
    private static function firstFrameOfRecursion(Throwable $throwable): ?string
    {
        $frames = [];

        foreach (Filter::framesFromThrowableAsStrings($throwable, false) as $frame) {
            $frames[] = $frame;
        }

        $candidates = self::cycleAtTopOf($frames);

        if ($candidates === null) {
            $candidates = self::framesThatOccurMoreThanOnce($frames);
        }

        $first = null;

        foreach ($candidates as $frame) {
            $frame = self::relativeToBaseDirectory($frame);

            if ($first === null || self::comesBefore($frame, $first)) {
                $first = $frame;
            }
        }

        return $first;
    }

    /**
     * Returns the frames of the shortest cycle that repeats at least three
     * times at the top of the stack trace, its innermost frames, or null when
     * there is no such cycle.
     *
     * The cycle can start with the second frame, as the innermost frame can
     * be where the error was raised rather than a call of the recursion: the
     * declaration of the function whose call exceeds the limit of Xdebug, for
     * instance.
     *
     * @param list<non-empty-string> $frames the frames of the stack trace, from the innermost one outward
     *
     * @return ?non-empty-list<non-empty-string>
     */
    private static function cycleAtTopOf(array $frames): ?array
    {
        foreach ([0, 1] as $start) {
            for ($length = 1; $length <= self::MAXIMUM_LENGTH_OF_CYCLE; $length++) {
                if ($start + self::REPETITIONS_OF_CYCLE * $length > count($frames)) {
                    break;
                }

                if (self::repeats($frames, $start, $length)) {
                    $cycle = array_slice($frames, $start, $length);

                    assert($cycle !== []);

                    return $cycle;
                }
            }
        }

        return null;
    }

    /**
     * Whether the frames from the start on repeat with the given length as
     * often as a cycle of the recursion must.
     *
     * @param list<non-empty-string> $frames
     * @param non-negative-int       $start
     * @param positive-int           $length
     */
    private static function repeats(array $frames, int $start, int $length): bool
    {
        $end = $start + (self::REPETITIONS_OF_CYCLE - 1) * $length;

        for ($i = $start; $i < $end; $i++) {
            if (!isset($frames[$i], $frames[$i + $length]) || $frames[$i] !== $frames[$i + $length]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<non-empty-string> $frames
     *
     * @return list<non-empty-string>
     */
    private static function framesThatOccurMoreThanOnce(array $frames): array
    {
        $seen     = [];
        $repeated = [];

        foreach ($frames as $frame) {
            if (isset($seen[$frame])) {
                $repeated[$frame] = $frame;

                continue;
            }

            $seen[$frame] = true;
        }

        return array_values($repeated);
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
     * @param non-empty-string $location
     *
     * @return non-empty-string
     */
    private static function relativeToBaseDirectory(string $location): string
    {
        $baseDirectory = self::baseDirectory();

        if ($baseDirectory === false) {
            // @codeCoverageIgnoreStart
            return $location;
            // @codeCoverageIgnoreEnd
        }

        $realpath = realpath($baseDirectory);

        if ($realpath !== false) {
            $baseDirectory = $realpath;
        }

        $prefix = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!self::startsWith($location, $prefix)) {
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

    /**
     * The directory of the configuration file, or the working directory when
     * there is no configuration file. The working directory is the one in
     * which the example was started, because PHPUnit restores it after the
     * example, should the example change it.
     */
    private static function baseDirectory(): false|string
    {
        $configuration = ConfigurationRegistry::get();

        if ($configuration->hasConfigurationFile()) {
            return dirname($configuration->configurationFile());
        }

        return getcwd();
    }

    /**
     * Paths are compared without regard to case on Windows, where the case
     * of the working directory, of its drive letter, for instance, can differ
     * from the one of the path of a file below it.
     */
    private static function startsWith(string $location, string $prefix): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // @codeCoverageIgnoreStart
            return strncasecmp($location, $prefix, strlen($prefix)) === 0;
            // @codeCoverageIgnoreEnd
        }

        return str_starts_with($location, $prefix);
    }
}
