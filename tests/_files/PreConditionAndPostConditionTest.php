<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class PreConditionAndPostConditionTest extends TestCase
{
    public static $preConditionWasVerified;

    public static $postConditionWasVerified;

    public static function resetProperties(): void
    {
        self::$preConditionWasVerified  = 0;
        self::$postConditionWasVerified = 0;
    }

    /**
     * @preCondition
     */
    public function verifyPreCondition(): void
    {
        self::$preConditionWasVerified++;
    }

    /**
     * @postCondition
     */
    public function verifyPostCondition(): void
    {
        self::$postConditionWasVerified++;
    }

    public function testSomething(): void
    {
    }
}
