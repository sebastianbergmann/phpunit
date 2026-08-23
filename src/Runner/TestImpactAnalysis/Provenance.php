<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

/**
 * Where what is known about a test comes from.
 *
 * What a test executed and what a test declares that it covers and uses are
 * not the same thing, and they are not equally trustworthy: the first is an
 * observation, the second is a promise that is only checked while code
 * coverage is collected. Data from the two cannot be mixed, and which of them
 * a file was written from is therefore part of what makes the file usable.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This enumeration is not covered by the backward compatibility promise for PHPUnit
 */
enum Provenance: string
{
    case ObservedExecution = 'observed-execution';
    case CoverageTargets   = 'coverage-targets';
}
