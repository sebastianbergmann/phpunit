<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class WithEnvironmentVariable
{
    /**
     * @var non-empty-string
     */
    private string $environmentVariableName;
    private null|string $value;

    /**
     * @param non-empty-string $environmentVariableName
     */
    public function __construct(string $environmentVariableName, null|string $value = null)
    {
        $this->environmentVariableName = $environmentVariableName;
        $this->value                   = $value;
    }

    /**
     * @return non-empty-string
     */
    public function environmentVariableName(): string
    {
        return $this->environmentVariableName;
    }

    public function value(): null|string
    {
        return $this->value;
    }
}
