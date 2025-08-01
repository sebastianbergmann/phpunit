<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default\ProgressPrinter;

use PHPUnit\Event\TestRunner\ChildProcessErrored;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ChildProcessErroredSubscriber extends Subscriber implements \PHPUnit\Event\TestRunner\ChildProcessErroredSubscriber
{
    public function notify(ChildProcessErrored $event): void
    {
        $this->printer()->childProcessErrored($event);
    }
}
