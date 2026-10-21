<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Exporting;

final class MoneyDeposited
{
    /**
     * @param list<string> $tags
     */
    public function __construct(public readonly AccountNumber $account, private readonly int $amount, private readonly array $tags = [])
    {
    }
}
