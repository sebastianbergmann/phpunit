<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\Worker;

/**
 * Process-local state that the worker-side subscriber sets and the tests
 * read: it can only be set inside the process that runs the tests.
 */
final class Probe
{
    public static int $preparationsSeen = 0;
}
