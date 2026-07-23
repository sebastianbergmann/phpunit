<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PhpConfiguration;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PhpConfigurationCheckResult
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var non-empty-string
     */
    private string $valueForConfiguration;
    private string $actualValue;
    private bool $ok;

    /**
     * @param non-empty-string $name
     * @param non-empty-string $valueForConfiguration
     */
    public function __construct(string $name, string $valueForConfiguration, string $actualValue, bool $ok)
    {
        $this->name                  = $name;
        $this->valueForConfiguration = $valueForConfiguration;
        $this->actualValue           = $actualValue;
        $this->ok                    = $ok;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function valueForConfiguration(): string
    {
        return $this->valueForConfiguration;
    }

    public function actualValue(): string
    {
        return $this->actualValue;
    }

    public function isOk(): bool
    {
        return $this->ok;
    }
}
