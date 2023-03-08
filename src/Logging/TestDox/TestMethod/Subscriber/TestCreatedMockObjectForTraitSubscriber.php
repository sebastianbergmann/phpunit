<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\Test\MockObjectForTraitCreated;
use PHPUnit\Event\Test\MockObjectForTraitCreatedSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestCreatedMockObjectForTraitSubscriber extends Subscriber implements MockObjectForTraitCreatedSubscriber
{
    public function notify(MockObjectForTraitCreated $event): void
    {
        $this->collector()->testCreatedTestDouble($event);
    }
}
