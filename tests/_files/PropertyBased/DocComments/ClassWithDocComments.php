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

use PHPUnit\Framework\Attributes\Small;

final class ClassWithDocComments
{
    /**
     * @param int<0, 10> $value
     */
    #[Small]
    public function methodWithDocComment(int $value): void
    {
    }

    public function methodWithoutDocComment(int $value): void
    {
    }

    public function method(): object
    {
        return new class
        {
            /**
             * @param int<0, 10> $value
             */
            public function method(int $value): void
            {
            }
        };
    }
}
