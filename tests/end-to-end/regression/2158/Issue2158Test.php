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

class Issue2158Test extends TestCase
{
    /**
     * Set constant in main process.
     */
    public function testSomething(): void
    {
        include __DIR__ . '/constant.inc';
        $this->assertTrue(true);
    }

    /**
     * Constant defined previously in main process constant should be available and
     * no errors should be yielded by reload of included files.
     *
     * @runInSeparateProcess
     */
    public function testSomethingElse(): void
    {
        $this->assertTrue(\defined('TEST_CONSTANT'));
    }
}
