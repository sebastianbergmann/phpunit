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
use PHPUnit\TextUI\Command;

class MyCommand extends Command
{
    public function __construct()
    {
        $this->longOptions['my-option=']      = 'myHandler';
        $this->longOptions['my-other-option'] = null;

        $eventEmitter = (new \PHPUnit\Framework\MockObject\Generator())->getMock(
            Event\Emitter::class,
            [],
            [],
            '',
            false
        );

        parent::__construct($eventEmitter);
    }

    public function myHandler($value): void
    {
        print __METHOD__ . " {$value}\n";
    }
}
