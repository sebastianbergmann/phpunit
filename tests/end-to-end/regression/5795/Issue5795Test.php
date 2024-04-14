<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5795;

use PHPUnit\Framework\TestCase;

final class Issue5795Test extends TestCase
{
    /**
     * @testWith		 [1]
     * 			 [2]
     * 			 [3]
     * 
     * @testdox This test should make phpunit spit a PHP Warning !
     */
    public function testExample($arg): void
    {
        $this->assertIsInt($arg);
    }
}
