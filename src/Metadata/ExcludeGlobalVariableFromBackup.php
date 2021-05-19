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
 * @psalm-immutable
 */
final class ExcludeGlobalVariableFromBackup extends Metadata
{
    private string $globalVariableName;

    public function __construct(string $globalVariableName)
    {
        $this->globalVariableName = $globalVariableName;
    }

    public function isExcludeGlobalVariableFromBackup(): bool
    {
        return true;
    }

    public function globalVariableName(): string
    {
        return $this->globalVariableName;
    }
}
