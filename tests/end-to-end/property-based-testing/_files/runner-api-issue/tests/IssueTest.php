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
use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class IssueTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testTriggersAWarning(int $value): void
    {
        trigger_error('message', E_USER_WARNING);

        $this->assertGreaterThanOrEqual(0, $value);
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testTriggersANotice(int $value): void
    {
        trigger_error('message', E_USER_NOTICE);

        $this->assertGreaterThanOrEqual(0, $value);
    }
}
