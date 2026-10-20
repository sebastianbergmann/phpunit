<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Console\Command;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FixCommandExitStatusCalculator
{
    // Exit status 1 is reserved for environment constraints not matched.
    public const EXIT_STATUS_FLAG_HAS_INVALID_FILES = 4;
    public const EXIT_STATUS_FLAG_HAS_CHANGED_FILES = 8;
    public const EXIT_STATUS_FLAG_HAS_INVALID_CONFIG = 16;
    public const EXIT_STATUS_FLAG_HAS_INVALID_FIXER_CONFIG = 32;
    public const EXIT_STATUS_FLAG_EXCEPTION_IN_APP = 64;

    public function calculate(
        bool $isDryRun,
        bool $hasChangedFiles,
        bool $hasInvalidErrors,
        bool $hasExceptionErrors,
        bool $hasLintErrorsAfterFixing
    ): int {
        $exitStatus = 0;

        if ($isDryRun) {
            if ($hasChangedFiles) {
                $exitStatus |= self::EXIT_STATUS_FLAG_HAS_CHANGED_FILES;
            }

            if ($hasInvalidErrors) {
                $exitStatus |= self::EXIT_STATUS_FLAG_HAS_INVALID_FILES;
            }
        }

        if ($hasExceptionErrors || $hasLintErrorsAfterFixing) {
            $exitStatus |= self::EXIT_STATUS_FLAG_EXCEPTION_IN_APP;
        }

        return $exitStatus;
    }
}
