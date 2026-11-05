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

final readonly class Customer
{
    private EmailAddress $emailAddress;

    public function __construct(EmailAddress $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function emailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }
}
