<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelInProcessUnit;

use function file_put_contents;
use function getenv;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

/**
 * The size it declares makes this test the last one to be dispatched: the
 * tests that declare no size are dispatched first.
 */
#[Small]
final class CMeanwhileTest extends TestCase
{
    public function testCreatesTheFileThatTheIsolatedTestWaitsFor(): void
    {
        $handshake = getenv('PHPUNIT_TEST_HANDSHAKE');

        $this->assertIsString($handshake);
        $this->assertNotFalse(file_put_contents($handshake . '.created', ''));
    }
}
