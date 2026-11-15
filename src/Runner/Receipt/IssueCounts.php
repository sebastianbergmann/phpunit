<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

/**
 * The numbers of the issues that have the same origin: they were either
 * triggered by tests or by the test runner.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class IssueCounts
{
    private int $errors;
    private int $deprecations;
    private int $notices;
    private int $warnings;
    private int $phpunitErrors;
    private int $phpunitDeprecations;
    private int $phpunitNotices;
    private int $phpunitWarnings;

    public function __construct(int $errors, int $deprecations, int $notices, int $warnings, int $phpunitErrors, int $phpunitDeprecations, int $phpunitNotices, int $phpunitWarnings)
    {
        $this->errors              = $errors;
        $this->deprecations        = $deprecations;
        $this->notices             = $notices;
        $this->warnings            = $warnings;
        $this->phpunitErrors       = $phpunitErrors;
        $this->phpunitDeprecations = $phpunitDeprecations;
        $this->phpunitNotices      = $phpunitNotices;
        $this->phpunitWarnings     = $phpunitWarnings;
    }

    public function errors(): int
    {
        return $this->errors;
    }

    public function deprecations(): int
    {
        return $this->deprecations;
    }

    public function notices(): int
    {
        return $this->notices;
    }

    public function warnings(): int
    {
        return $this->warnings;
    }

    public function phpunitErrors(): int
    {
        return $this->phpunitErrors;
    }

    public function phpunitDeprecations(): int
    {
        return $this->phpunitDeprecations;
    }

    public function phpunitNotices(): int
    {
        return $this->phpunitNotices;
    }

    public function phpunitWarnings(): int
    {
        return $this->phpunitWarnings;
    }
}
