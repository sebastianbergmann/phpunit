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

final readonly class ClassWithPromotedConstructorParameters
{
    /**
     * @param int<1, 10> $documentedTwice
     */
    public function __construct(
        /** @var positive-int */
        public int $positive,
        /** @var list<Money> the amounts */
        public array $amounts,
        /** @var negative-int */
        public int $documentedTwice,
        /** The description of a parameter whose type is not documented. */
        public bool $native,
    ) {
    }
}
