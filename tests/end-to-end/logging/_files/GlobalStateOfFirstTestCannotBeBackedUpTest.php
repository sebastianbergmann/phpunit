<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Logging;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GlobalStateOfFirstTestCannotBeBackedUpValue
{
    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
        throw new RuntimeException('message');
    }
}

final class GlobalStateOfFirstTestCannotBeBackedUpTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['globalStateOfFirstTestCannotBeBackedUp'] = new GlobalStateOfFirstTestCannotBeBackedUpValue;
    }

    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['globalStateOfFirstTestCannotBeBackedUp']);
    }

    #[BackupGlobals(true)]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
