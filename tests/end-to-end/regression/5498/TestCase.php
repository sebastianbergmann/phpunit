<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5498;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    #[Before]
    protected function parentBefore(): void
    {
    }

    #[After]
    protected function parentAfter(): void
    {
    }
}
