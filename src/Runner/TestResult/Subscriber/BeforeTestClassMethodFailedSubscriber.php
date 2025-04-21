<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailedSubscriber;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BeforeTestClassMethodFailedSubscriber extends Subscriber implements BeforeFirstTestMethodFailedSubscriber
{
    public function notify(BeforeFirstTestMethodFailed $event): void
    {
        $this->collector()->beforeTestClassMethodFailed($event);
    }
}
