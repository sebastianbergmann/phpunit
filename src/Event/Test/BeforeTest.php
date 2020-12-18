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

final class BeforeTest implements Event
{
    private Test $test;

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    public function test(): Test
    {
        return $this->test;
    }
}
