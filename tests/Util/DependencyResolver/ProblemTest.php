<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\DependencyResolver;

use PHPUnit\Framework\TestCase;

class ProblemTest extends TestCase
{
    public function testGetName()
    {
        $problem = new Problem('testName', new \stdClass());
        $this->assertSame('testName', $problem->getName());
    }

    public function testGetObject()
    {
        $object = new \stdClass();
        $problem = new Problem('testName', $object);
        $this->assertSame($object, $problem->getObject());
    }
}
