<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ProvidedData
{
    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @var array<mixed>
     */
    private array $value;

    /**
     * @param non-empty-string $label
     * @param array<mixed>     $value
     */
    public function __construct(string $label, array $value)
    {
        $this->label = $label;
        $this->value = $value;
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return array<mixed>
     */
    public function value(): array
    {
        return $this->value;
    }
}
