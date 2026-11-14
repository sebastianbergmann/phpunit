<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use const PHP_EOL;
use function print_r;
use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ExitTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        // takes longer than the time limit of the example that ended the
        // process
        usleep(1_500_000);

        print_r('tearDownAfterClass() completed' . PHP_EOL);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testEndsTheProcess(int $value): void
    {
        exit(3);
    }
}
