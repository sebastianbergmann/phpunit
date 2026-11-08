<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * A value that an example of a property drew with draw(), exported as PHP
 * code, with the label that was passed to draw().
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DrawnValue
{
    /**
     * @var ?non-empty-string
     */
    private ?string $label;
    private string $value;

    /**
     * @param ?non-empty-string $label
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(?string $label, string $value)
    {
        $this->label = $label;
        $this->value = $value;
    }

    /**
     * The label that was passed to draw(), null when none was passed.
     *
     * @return ?non-empty-string
     */
    public function label(): ?string
    {
        return $this->label;
    }

    /**
     * The value, exported as PHP code.
     */
    public function value(): string
    {
        return $this->value;
    }
}
