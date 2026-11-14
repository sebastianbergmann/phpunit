<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * Another way in which a property was falsified while its counterexample was
 * shrunk, with the simplest counterexample that falsified it that way.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class OtherFalsification
{
    /**
     * @var non-empty-string
     */
    private string $description;
    private Counterexample $counterexample;

    /**
     * @param non-empty-string $description
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(string $description, Counterexample $counterexample)
    {
        $this->description    = $description;
        $this->counterexample = $counterexample;
    }

    /**
     * Describes how the property was falsified, for instance
     * "RuntimeException at tests/FooTest.php:12".
     *
     * @return non-empty-string
     */
    public function description(): string
    {
        return $this->description;
    }

    public function counterexample(): Counterexample
    {
        return $this->counterexample;
    }
}
