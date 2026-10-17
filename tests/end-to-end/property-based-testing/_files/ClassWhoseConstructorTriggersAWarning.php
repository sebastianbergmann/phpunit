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

use const E_USER_WARNING;
use function trigger_error;

final readonly class ClassWhoseConstructorTriggersAWarning
{
    private int $value;

    public function __construct(int $value)
    {
        trigger_error('warning triggered by constructor', E_USER_WARNING);

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
