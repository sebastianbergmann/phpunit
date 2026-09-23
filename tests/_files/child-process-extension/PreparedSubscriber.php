<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ChildProcessExtension;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber as PreparedSubscriberInterface;

final class PreparedSubscriber implements PreparedSubscriberInterface
{
    public function notify(Prepared $event): void
    {
    }
}
