<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use Closure;
use Throwable;

final readonly class DomainEvent
{
    public function __construct(public string $name, public ?Throwable $previous = null, public ?Closure $callback = null)
    {
    }
}
