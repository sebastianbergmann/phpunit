<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\StateMachine;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Rule;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * Closures in attributes require PHP 8.5.
 */
final class StateMachineWithGeneratorSpecifiedWithClosure
{
    #[Rule]
    public function step(
        #[ForAll(static function (): ValueGenerator
        {
            return Generators::elements(['a', 'b']);
        })]
        string $name,
    ): void {
    }
}
