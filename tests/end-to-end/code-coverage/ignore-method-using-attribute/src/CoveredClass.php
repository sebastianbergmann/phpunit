<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\IgnoreMethodUsingAttribute;

final class CoveredClass
{
    public function m(): bool
    {
        return $this->n();
    }

    private function n(): bool
    {
        return true;
    }
}
