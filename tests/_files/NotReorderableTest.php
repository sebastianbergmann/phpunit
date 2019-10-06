<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Event;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;

class NotReorderableTest implements Test
{
    public function count()
    {
        return 1;
    }

    public function run(Event\Dispatcher $dispatcher, TestResult $result): void
    {
    }

    public function provides(): array
    {
        return [];
    }

    public function requires(): array
    {
        return [];
    }
}
