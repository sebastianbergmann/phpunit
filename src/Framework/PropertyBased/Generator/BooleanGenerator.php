<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

/**
 * Generates false (choice 0) and true (choice 1).
 *
 * @template-extends AbstractGenerator<bool>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class BooleanGenerator extends AbstractGenerator
{
    private UniformDistribution $distribution;

    public function __construct()
    {
        $this->distribution = new UniformDistribution;
    }

    protected function generateValue(ChoiceSource $source): bool
    {
        return $source->draw(1, $this->distribution) === 1;
    }
}
