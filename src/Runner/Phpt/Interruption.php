<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

/**
 * The signal through which the caller that drives the generator returned by
 * TestCase::execute() declares that it has abandoned the test.
 *
 * An abandoned test's generator, when driven to completion, runs only what
 * must still run — the --CLEAN-- section, when the --FILE-- section has
 * already run — and skips everything else (see TestCase::execute()).
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Interruption
{
    private bool $interrupted = false;

    public function interrupt(): void
    {
        $this->interrupted = true;
    }

    public function interrupted(): bool
    {
        return $this->interrupted;
    }
}
