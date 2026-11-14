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

final readonly class Notification
{
    /**
     * @param int<0, 9> $priority
     */
    public function __construct(public string $message, public ?Closure $formatter = null, public int $priority = 5)
    {
    }
}
