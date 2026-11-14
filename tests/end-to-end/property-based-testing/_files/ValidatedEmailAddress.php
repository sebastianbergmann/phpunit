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

use function str_contains;
use InvalidArgumentException;

final readonly class ValidatedEmailAddress
{
    public string $value;

    public function __construct(string $value)
    {
        if (!str_contains($value, '@')) {
            throw new InvalidArgumentException('Not an e-mail address');
        }

        $this->value = $value;
    }
}
