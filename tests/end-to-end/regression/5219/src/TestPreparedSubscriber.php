<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5219;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use RuntimeException;

final class TestPreparedSubscriber implements PreparedSubscriber
{
    public function notify(Prepared $event): void
    {
        throw new RuntimeException('message');
    }
}
