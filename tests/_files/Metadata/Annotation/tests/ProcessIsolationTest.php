<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Annotation;

use PHPUnit\Framework\TestCase;

/**
 * @runClassInSeparateProcess
 *
 * @runTestsInSeparateProcesses
 */
final class ProcessIsolationTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testOne(): void
    {
    }
}
