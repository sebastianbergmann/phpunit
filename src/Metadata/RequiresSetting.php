<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class RequiresSetting extends Metadata
{
    private readonly string $setting;
    private readonly string $value;

    protected function __construct(int $level, string $setting, string $value)
    {
        parent::__construct($level);

        $this->setting = $setting;
        $this->value   = $value;
    }

    public function isRequiresSetting(): bool
    {
        return true;
    }

    public function setting(): string
    {
        return $this->setting;
    }

    public function value(): string
    {
        return $this->value;
    }
}
