<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6382;

use PHPUnit\Framework\TestCase;
use Greg0ire\PhpunitReproducer\Child6382;

class Issue6382Test extends TestCase
{
    public function testExample(): void
    {
        require_once __DIR__.'/Ancestor.php';
        require_once __DIR__.'/Child.php';

        new Child6382();
    }
}
