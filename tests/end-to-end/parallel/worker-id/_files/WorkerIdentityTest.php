<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerIdentity;

use function getenv;
use function strlen;
use function substr;
use PHPUnit\Framework\TestCase;

final class WorkerIdentityTest extends TestCase
{
    public function testWorkerIdentityIsExposedToTheTest(): void
    {
        $id    = getenv('PHPUNIT_WORKER_ID');
        $token = getenv('PHPUNIT_WORKER_TOKEN');

        $this->assertIsString($id);
        $this->assertMatchesRegularExpression('/^\d+$/', $id);

        $this->assertIsString($token);
        $this->assertMatchesRegularExpression('/^\d+_[0-9a-f]{32}$/', $token);

        $this->assertSame($id . '_', substr($token, 0, strlen($id) + 1));
    }
}
