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

use Closure;

final readonly class ClassWithConstructorParametersPassedByReference
{
    /**
     * @var non-empty-list<int>
     */
    public array $values;
    public ?Closure $callback;

    /**
     * @param non-empty-list<int> $values
     */
    public function __construct(array &$values, ?Closure &$callback = null)
    {
        $this->values   = $values;
        $this->callback = $callback;

        $values   = [];
        $callback = static fn (): bool => true;
    }
}
