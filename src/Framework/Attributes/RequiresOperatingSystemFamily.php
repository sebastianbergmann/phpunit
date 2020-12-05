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
class RequiresOperatingSystemFamily
{
    private string $operatingSystemFamily;

    public function __construct(string $operatingSystemFamily)
    {
        $this->operatingSystemFamily = $operatingSystemFamily;
    }

    public function operatingSystemFamily(): string
    {
        return $this->operatingSystemFamily;
    }
}
