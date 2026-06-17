<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

/**
 * The reason why a child process was started.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
enum ChildProcessReason: string
{
    case TestRequiringProcessIsolation = 'test requiring process isolation';
    case PhptTest                      = 'FILE section of a PHPT test';
    case PhptSkipIfSection             = 'SKIPIF section of a PHPT test';
    case PhptCleanSection              = 'CLEAN section of a PHPT test';
    case ParallelWorker                = 'worker for parallel test execution';
}
