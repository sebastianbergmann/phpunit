<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Derivation;

use function str_contains;
use InvalidArgumentException;

final readonly class ClassWithConstructorThatValidatesItsArguments
{
    public string $emailAddress;
    public Money $balance;

    public function __construct(string $emailAddress, Money $balance)
    {
        if (!str_contains($emailAddress, '@')) {
            throw new InvalidArgumentException('Not an e-mail address');
        }

        $this->emailAddress = $emailAddress;
        $this->balance      = $balance;
    }
}
