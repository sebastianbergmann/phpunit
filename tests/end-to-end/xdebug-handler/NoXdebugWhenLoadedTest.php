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
use PHPUnit\Util\XdebugManager;

class NoXdebugWhenLoadedTest extends TestCase
{
    public function testNoXdebugWhenLoaded(): void
    {
        // Check that we have restart settings
        $this->assertNotNull(XdebugManager::getRestartSettings());
        $this->assertFalse(\extension_loaded('xdebug'));
    }
}
