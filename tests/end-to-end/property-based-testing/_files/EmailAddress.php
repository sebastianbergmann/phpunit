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

final readonly class EmailAddress
{
    private string $address;

    public static function fromString(string $address): self
    {
        return new self($address);
    }

    private function __construct(string $address)
    {
        $this->address = $address;
    }

    public function asString(): string
    {
        return $this->address;
    }
}
