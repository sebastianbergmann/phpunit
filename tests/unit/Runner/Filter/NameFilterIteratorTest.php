<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use PHPUnit\Framework\TestCase;

class NameFilterIteratorTest extends TestCase
{
    public function testCaseSensitiveMatch()
    {
        $iterator = $this->getTestSuiteItteratorMock();
        $filter   = new NameFilterIterator($iterator, 'Success');
        $this->assertTrue((bool) $filter->accept());
    }

    public function testCaseInsensitiveMatch()
    {
        $iterator = $this->getTestSuiteItteratorMock();
        $filter   = new NameFilterIterator($iterator, 'success');
        $this->assertTrue((bool) $filter->accept());
    }

    /**
     * @return \PHPUnit\Framework\TestSuiteIterator
     */
    private function getTestSuiteItteratorMock()
    {
        $success   = new \Success();
        $iterator = $this->createMock(\PHPUnit\Framework\TestSuiteIterator::class);
        $iterator->expects($this->once())->method('current')->willReturn($success);

        return $iterator;
    }
}
