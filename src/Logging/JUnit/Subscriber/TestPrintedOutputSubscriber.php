<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\JUnit;

use PHPUnit\Event\Test\OutputPrinted;
use PHPUnit\Event\Test\OutputPrintedSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestPrintedOutputSubscriber extends Subscriber implements OutputPrintedSubscriber
{
    public function notify(OutputPrinted $event): void
    {
        $this->logger()->testPrintedOutput($event);
    }
}
