<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Event;
use PHPUnit\Event\NamedType;
use PHPUnit\Event\Type;

final class BeforeTestSuite implements Event
{
    public function type(): Type
    {
        return new NamedType('test-suite-started');
    }
}
