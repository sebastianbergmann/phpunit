<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDataProvider;

use function getenv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FailingInWorkerDataTest extends TestCase
{
    /**
     * A data provider that depends on state only the main process has: it
     * provides its data there, and fails in a worker process.
     */
    public static function providerThatFailsInAWorker(): array
    {
        if (getenv('PHPUNIT_WORKER_ID') !== false) {
            throw new RuntimeException('the state this data provider depends on does not exist in a worker process');
        }

        return [
            [true],
        ];
    }

    #[DataProvider('providerThatFailsInAWorker')]
    public function testWithADataProviderThatFailsInAWorker(bool $value): void
    {
        $this->assertTrue($value);
    }
}
