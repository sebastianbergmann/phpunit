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

use const E_WARNING;
use function restore_error_handler;
use function set_error_handler;
use function var_export;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class VariableExporter
{
    public function export(mixed $variable): ExportedVariable
    {
        $warningWasTriggered = false;

        set_error_handler(
            static function (int $errorNumber, string $errorString) use (&$warningWasTriggered): ?bool
            {
                $warningWasTriggered = true;

                return null;
            },
            E_WARNING
        );

        $exportedVariable = var_export($variable, true);

        restore_error_handler();

        return ExportedVariable::from($exportedVariable, $warningWasTriggered);
    }
}
