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

use PHPUnit\Event\Test\MockObjectCreated;
use PHPUnit\Event\Test\MockObjectCreatedSubscriber;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestCreatedMockObjectSubscriber extends Subscriber implements MockObjectCreatedSubscriber
{
    public function notify(MockObjectCreated $event): void
    {
        $this->collector()->testCreatedTestDouble($event);
    }
}
