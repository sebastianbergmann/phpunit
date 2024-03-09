<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverage;

final class Method
{
    public function greet(): string
    {
        return $this->internalMethod();
    }

    public function unusedPublicMethod(): string
    {
        return 'never returned';
    }

    private function internalMethod(): string
    {
        return 'Hello, World!';
    }
}
