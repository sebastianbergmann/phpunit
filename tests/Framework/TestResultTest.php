<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

class TestResultTest extends TestCase
{
    public function testRemoveListenerRemovesOnlyExpectedListener(): void
    {
        $result         = new TestResult();
        $firstListener  = $this->getMockBuilder(TestListener::class)->getMock();
        $secondListener = $this->getMockBuilder(TestListener::class)->getMock();
        $thirdListener  = $this->getMockBuilder(TestListener::class)->getMock();
        $result->addListener($firstListener);
        $result->addListener($secondListener);
        $result->addListener($thirdListener);
        $result->addListener($firstListener);
        $this->assertAttributeEquals(
            [$firstListener, $secondListener, $thirdListener, $firstListener],
            'listeners',
            $result
        );
        $result->removeListener($firstListener);
        $this->assertAttributeEquals(
            [1 => $secondListener, 2 => $thirdListener],
            'listeners',
            $result
        );
    }
}
