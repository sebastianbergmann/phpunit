<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Job
{
    /**
     * @psalm-var non-empty-string
     */
    private string $code;
    private array $phpSettings;

    /**
     * @psalm-var array<string, string>
     */
    private array $environmentVariables;

    /**
     * @psalm-var list<non-empty-string>
     */
    private array $arguments;

    /**
     * @psalm-var ?non-empty-string
     */
    private ?string $input;
    private bool $redirectErrors;

    /**
     * @psalm-param non-empty-string $code
     * @psalm-param array<string, string> $environmentVariables
     * @psalm-param list<non-empty-string> $arguments
     * @psalm-param ?non-empty-string $input
     */
    public function __construct(string $code, array $phpSettings = [], array $environmentVariables = [], array $arguments = [], ?string $input = null, bool $redirectErrors = false)
    {
        $this->code                 = $code;
        $this->phpSettings          = $phpSettings;
        $this->environmentVariables = $environmentVariables;
        $this->arguments            = $arguments;
        $this->input                = $input;
        $this->redirectErrors       = $redirectErrors;
    }

    /**
     * @psalm-return non-empty-string
     */
    public function code(): string
    {
        return $this->code;
    }

    public function phpSettings(): array
    {
        return $this->phpSettings;
    }

    /**
     * @psalm-assert-if-true !empty $this->environmentVariables
     */
    public function hasEnvironmentVariables(): bool
    {
        return $this->environmentVariables !== [];
    }

    /**
     * @psalm-return array<string, string>
     */
    public function environmentVariables(): array
    {
        return $this->environmentVariables;
    }

    /**
     * @psalm-assert-if-true !empty $this->arguments
     */
    public function hasArguments(): bool
    {
        return $this->arguments !== [];
    }

    /**
     * @psalm-return list<non-empty-string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @psalm-assert-if-true !empty $this->input
     */
    public function hasInput(): bool
    {
        return $this->input !== null;
    }

    /**
     * @psalm-return non-empty-string
     *
     * @throws PhpProcessException
     */
    public function input(): string
    {
        if ($this->input === null) {
            throw new PhpProcessException('No input specified');
        }

        return $this->input;
    }

    public function redirectErrors(): bool
    {
        return $this->redirectErrors;
    }
}
