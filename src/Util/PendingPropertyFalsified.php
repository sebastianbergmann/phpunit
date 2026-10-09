<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Test\PropertyFalsified;

/**
 * Keeps the Test\PropertyFalsified event, which is dispatched before the
 * Test\Failed or Test\Errored event of the property, until the failure or
 * error is reported together with how the property was falsified.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PendingPropertyFalsified
{
    private ?PropertyFalsified $event = null;

    public function remember(PropertyFalsified $event): void
    {
        $this->event = $event;
    }

    /**
     * Returns the event that was remembered when it is the one for the given
     * test, and forgets it in any case.
     */
    public function takeFor(Test $test): ?PropertyFalsified
    {
        $event = $this->event;

        $this->event = null;

        if ($event === null || $event->test()->id() !== $test->id()) {
            return null;
        }

        return $event;
    }
}
