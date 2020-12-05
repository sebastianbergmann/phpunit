<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequiresOperatingSystem
{
    private string $regularExpression;

    public function __construct(string $regularExpression)
    {
        $this->regularExpression = $regularExpression;
    }

    public function regularExpression(): string
    {
        return $this->regularExpression;
    }
}
