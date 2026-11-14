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

use const E_USER_WARNING;
use function restore_error_handler;
use function trigger_error;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class RemovesAnErrorHandlerBeforeFirstTestTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        restore_error_handler();
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testTriggersAWarning(int $value): void
    {
        trigger_error('triggered in a test method', E_USER_WARNING);

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
