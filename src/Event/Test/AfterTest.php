<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\Event;
use PHPUnit\Event\Type;

final class AfterTest implements Event
{
    private Test $test;

    private Result $result;

    public function __construct(Test $test, Result $result)
    {
        $this->test   = $test;
        $this->result = $result;
    }

    public function type(): Type
    {
        return new AfterTestType();
    }

    public function test(): Test
    {
        return $this->test;
    }

    public function result(): Result
    {
        return $this->result;
    }
}
