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

final readonly class ObjectWithPropertyThatIsNotPromoted
{
    public int $double;

    public function __construct(public int $value)
    {
        $this->double = $value * 2;
    }
}
