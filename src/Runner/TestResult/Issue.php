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

use function array_keys;
use function count;
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
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $description
     */
    public static function from(string $file, int $line, string $description, Test $triggeringTest): self
    {
        return new self($file, $line, $description, $triggeringTest);
    }

    /**
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $description
     */
    private function __construct(string $file, int $line, string $description, Test $triggeringTest)
    {
        $this->file        = $file;
        $this->line        = $line;
        $this->description = $description;

        $this->triggeringTests = [
            $triggeringTest->id() => [
                'test'  => $triggeringTest,
                'count' => 1,
            ],
        ];
    }

    public function triggeredBy(Test $test): void
    {
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

    public function triggeredInTest(): bool
    {
        return count($this->triggeringTests) === 1 &&
               $this->file === $this->triggeringTests[array_keys($this->triggeringTests)[0]]['test']->file();
    }
}
