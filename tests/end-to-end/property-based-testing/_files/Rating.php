<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

final readonly class Rating
{
    /**
     * @param non-empty-string $product
     */
    public function __construct(
        /** @var int<1, 5> */
        public int $stars,
        /** @var 'a'|'b' */
        public string $product,
        /**
         * @var list<non-empty-string> the tags of the rating
         */
        public array $tags,
        public bool $verified,
    ) {
    }
}
