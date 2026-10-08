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

/**
 * Generates lists that consist of one value of each of the given generators.
 *
 * @template-extends AbstractGenerator<list<mixed>>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TupleGenerator extends AbstractGenerator
{
    /**
     * @var list<AbstractGenerator<mixed>>
     */
    private array $generators;

    /**
     * @param list<AbstractGenerator<mixed>> $generators
     */
    public function __construct(array $generators)
    {
        $this->generators = $generators;
    }

    protected function generateValue(ChoiceSource $source): array
    {
        $tuple = [];

        foreach ($this->generators as $generator) {
            $tuple[] = $generator->generate($source);
        }

        return $tuple;
    }
}
