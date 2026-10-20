<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;

/**
 * Draws the values that the body of an example of a property draws with
 * TestCase::draw() from the choice source that generated its arguments, so
 * that they are replayed and shrunk together with the arguments.
 *
 * The values that the minimal counterexample draws are reported. They are
 * exported when they are drawn, before the example can change the state of
 * an object it drew.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DrawnValues
{
    private readonly ChoiceSource $source;

    /**
     * The exporter for the values that are reported, null when they are not
     * reported.
     */
    private readonly ?CounterexampleExporter $exporter;

    /**
     * @var list<array{label: ?non-empty-string, value: string}>
     */
    private array $exported = [];

    /**
     * Draws values that are not reported, for an example that is generated or
     * for a shrinking attempt.
     */
    public static function from(ChoiceSource $source): self
    {
        return new self($source, null);
    }

    /**
     * Draws values that are reported, for the final replay of the minimal
     * counterexample.
     */
    public static function reportedFrom(ChoiceSource $source, CounterexampleExporter $exporter): self
    {
        return new self($source, $exporter);
    }

    private function __construct(ChoiceSource $source, ?CounterexampleExporter $exporter)
    {
        $this->source   = $source;
        $this->exporter = $exporter;
    }

    /**
     * @template T
     *
     * @param ValueGenerator<T> $generator
     * @param ?non-empty-string $label     describes the value in the report
     *
     * @throws ExampleRejectedException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return T
     */
    public function draw(ValueGenerator $generator, ?string $label): mixed
    {
        $value = AbstractGenerator::from($generator)->generate($this->source);

        if ($this->exporter !== null) {
            $this->exported[] = ['label' => $label, 'value' => $this->exporter->export($value)];
        }

        return $value;
    }

    /**
     * The values that were drawn, with their labels, exported as PHP code,
     * in the order in which they were drawn. Values that are not reported are
     * not exported.
     *
     * @return list<array{label: ?non-empty-string, value: string}>
     */
    public function exported(): array
    {
        return $this->exported;
    }
}
