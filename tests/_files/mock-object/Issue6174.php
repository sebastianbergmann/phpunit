<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\MockObject;

interface Issue6174
{
    public function methodNullDefault(?string $param, ?string $nullDefault = null): string;

    public function methodStringDefault(?string $param, ?string $stringDefault = 'something'): string;
}
