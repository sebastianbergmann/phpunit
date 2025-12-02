<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Phar;

use PHPUnit\Framework\TestCase;

final class ScopedTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testIsScoped(): void
    {
        $this->assertTrue(class_exists('\PHPUnitPHAR\TheSeer\Tokenizer\XMLSerializer'));
        $this->assertFalse(class_exists('\TheSeer\Tokenizer\XMLSerializer'));
    }

}
