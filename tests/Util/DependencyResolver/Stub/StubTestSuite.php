<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\DependencyResolver\Stub;

use PHPUnit\Framework\TestSuite;

class StubTestSuite extends TestSuite
{
    /**
     * @param string $theClass
     * @param string $name
     */
    public function __construct($theClass = '', $name = '')
    {
        $this->name = $name;
    }
}
