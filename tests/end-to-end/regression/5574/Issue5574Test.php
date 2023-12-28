<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5574;

use Error;
use Exception;
use PHPUnit\Framework\TestCase;

final class Issue5574Test extends TestCase
{
    public function testThrownWrappedThrowablesOutputsCorrectStackTraceForEach(): void
    {
        $innerException = new Error('Inner Exception');
        $outerException = new Exception('My exception', 0, $innerException);

        throw $outerException;
    }
}
