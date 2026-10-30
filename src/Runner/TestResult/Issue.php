<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult\Issues;

use function array_filter;
use function array_values;
use function assert;
use function count;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Test;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Issue
{
    /**
     * @var non-empty-string
     */
    private readonly string $file;

    /**
     * @var positive-int
     */
    private readonly int $line;

    /**
     * @var non-empty-string
     */
    private readonly string $description;

    /**
     * @var non-empty-array<non-empty-string, array{test: Test, count: int}>
     */
    private array $triggeringTests;

    /**
     * @var ?non-empty-string
     */
    private ?string $stackTrace;

    /**
     * @var array{self: non-negative-int, direct: non-negative-int, indirect: non-negative-int}
     */
    private array $triggerTypes = ['self' => 0, 'direct' => 0, 'indirect' => 0];

    /**
     * @param non-empty-string  $file
     * @param positive-int      $line
     * @param non-empty-string  $description
     * @param ?non-empty-string $stackTrace
     */
    public static function from(string $file, int $line, string $description, Test $triggeringTest, ?string $stackTrace = null, ?IssueTrigger $trigger = null): self
    {
        return new self($file, $line, $description, $triggeringTest, $stackTrace, $trigger);
    }

    /**
     * @param non-empty-string  $file
     * @param positive-int      $line
     * @param non-empty-string  $description
     * @param ?non-empty-string $stackTrace
     */
    private function __construct(string $file, int $line, string $description, Test $triggeringTest, ?string $stackTrace, ?IssueTrigger $trigger)
    {
        $this->file        = $file;
        $this->line        = $line;
        $this->description = $description;
        $this->stackTrace  = $stackTrace;

        $this->triggeringTests = [
            $triggeringTest->id() => [
                'test'  => $triggeringTest,
                'count' => 1,
            ],
        ];

        $this->registerTrigger($trigger);
    }

    public function triggeredBy(Test $test, ?IssueTrigger $trigger = null): void
    {
        $this->registerTrigger($trigger);

        if (isset($this->triggeringTests[$test->id()])) {
            $this->triggeringTests[$test->id()]['count']++;

            return;
        }

        $this->triggeringTests[$test->id()] = [
            'test'  => $test,
            'count' => 1,
        ];
    }

    /**
     * @return non-empty-string
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * @return positive-int
     */
    public function line(): int
    {
        return $this->line;
    }

    /**
     * @return non-empty-string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return non-empty-array<non-empty-string, array{test: Test, count: int}>
     */
    public function triggeringTests(): array
    {
        return $this->triggeringTests;
    }

    /**
     * @return positive-int
     */
    public function numberOfTimesTriggered(): int
    {
        $count = 0;

        foreach ($this->triggeringTests as $triggeringTest) {
            $count += $triggeringTest['count'];
        }

        assert($count > 0);

        return $count;
    }

    /**
     * @phpstan-assert-if-true !null $this->stackTrace
     */
    public function hasStackTrace(): bool
    {
        return $this->stackTrace !== null;
    }

    /**
     * @return ?non-empty-string
     */
    public function stackTrace(): ?string
    {
        return $this->stackTrace;
    }

    public function triggeredInTest(): bool
    {
        return count($this->triggeringTests) === 1 &&
               $this->file === array_values($this->triggeringTests)[0]['test']->file();
    }

    /**
     * @return array{self?: positive-int, direct?: positive-int, indirect?: positive-int}
     */
    public function triggerTypes(): array
    {
        return array_filter($this->triggerTypes, static fn (int $count): bool => $count > 0);
    }

    private function registerTrigger(?IssueTrigger $trigger): void
    {
        if ($trigger === null) {
            return;
        }

        if ($trigger->isSelf()) {
            $this->triggerTypes['self']++;
        } elseif ($trigger->isDirect()) {
            $this->triggerTypes['direct']++;
        } elseif ($trigger->isIndirect()) {
            $this->triggerTypes['indirect']++;
        }
    }
}
