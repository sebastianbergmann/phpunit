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

class MultipleBootstrapsTest extends TestCase
{
    public function testMultipleBootstrapsLoadCorrectly(): void
    {
        $this->assertTrue(defined('PHPUNIT_MULTIPLE_BOOTSTRAPS_GLOBAL'), 'Global bootstrap did not load.');
        $this->assertTrue(defined('PHPUNIT_MULTIPLE_BOOTSTRAPS_UNIT'), 'Unit Test Bootstrap did not load.');
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultipleBootstrapsLoadCorrectlyInProcessIsolation(): void
    {
        $this->assertTrue(defined('PHPUNIT_MULTIPLE_BOOTSTRAPS_GLOBAL'), 'Global bootstrap did not load.');
        $this->assertTrue(defined('PHPUNIT_MULTIPLE_BOOTSTRAPS_UNIT'), 'Unit Test Bootstrap did not load.');
    }
}
