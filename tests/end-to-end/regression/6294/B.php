<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use const PHP_EOL;

class B extends A
{
    // incorrect access level
    protected function someFunction(): void
    {
        parent::someFunction();

        print 'B::someFunction' . PHP_EOL;
    }
}
