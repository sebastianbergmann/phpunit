<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExportedVariable
{
    private string $export;
    private bool $warningWasTriggered;

    public static function from(?string $export, bool $warningWasTriggered): self
    {
        return new self((string) $export, $warningWasTriggered);
    }

    public function __construct(string $export, bool $warningWasTriggered)
    {
        $this->export              = $export;
        $this->warningWasTriggered = $warningWasTriggered;
    }

    public function asVarExportString(): string
    {
        return $this->export;
    }

    public function asValue(): mixed
    {
        return eval('return ' . $this->asVarExportString() . ';');
    }

    public function warningWasTriggered(): bool
    {
        return $this->warningWasTriggered;
    }
}
