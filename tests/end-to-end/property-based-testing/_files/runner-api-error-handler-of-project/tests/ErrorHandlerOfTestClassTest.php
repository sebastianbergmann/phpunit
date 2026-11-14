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

use const E_USER_NOTICE;
use function restore_error_handler;
use function set_error_handler;
use function trigger_error;
use ErrorException;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ErrorHandlerOfTestClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        set_error_handler(
            static function (int $errorNumber, string $errorString, string $errorFile, int $errorLine): bool
            {
                if ($errorNumber !== E_USER_NOTICE) {
                    return false;
                }

                throw new ErrorException($errorString, 0, $errorNumber, $errorFile, $errorLine);
            },
        );
    }

    public static function tearDownAfterClass(): void
    {
        restore_error_handler();
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testTriggersANotice(int $value): void
    {
        trigger_error('notice', E_USER_NOTICE);

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
