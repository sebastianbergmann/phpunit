<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\DocComments;

use SensitiveParameter;

final class ClassWithPromotedConstructorParameters
{
    /**
     * @param int<0, 10> $documentedTwice
     */
    public function __construct(
        /** @var positive-int */
        public int $beforeParameter,
        /**
         * @var non-empty-string
         */
        #[SensitiveParameter]
        public string $beforeAttribute,
        /** @var negative-int */
        public int $documentedTwice,
        /** @var non-empty-string */
        string $notPromoted,
        public bool $withoutDocComment,
    ) {
    }
}
