<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\Failing;

use function getenv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DataProviderFailsInWorkerTest extends TestCase
{
    /**
     * Provides its data in the main process and fails in a worker process,
     * so that the only unit the worker is given cannot be run.
     */
    public static function provider(): array
    {
        if (getenv('PHPUNIT_WORKER_ID') !== false) {
            throw new RuntimeException('the state this data provider depends on does not exist in a worker process');
        }

        return [
            [true],
        ];
    }

    #[DataProvider('provider')]
    public function testWithADataProviderThatFailsInAWorker(bool $value): void
    {
        $this->assertTrue($value);
    }
}
