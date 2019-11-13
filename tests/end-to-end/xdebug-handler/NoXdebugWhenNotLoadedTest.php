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

class NoXdebugWhenNotLoadedTest extends TestCase
{
    public function testNoXdebugWhenNotLoaded(): void
    {
        // If Xdebug was not loaded, there will be no restart settings
        $this->assertNull(XdebugManager::getRestartSettings());
        $this->assertFalse(\extension_loaded('xdebug'));
    }
}
