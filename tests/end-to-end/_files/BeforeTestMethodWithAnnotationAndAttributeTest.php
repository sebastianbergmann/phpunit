<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\DeprecatedAnnotationsTestFixture;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

final class BeforeTestMethodWithAnnotationAndAttributeTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @before
     */
    #[Before]
    protected function beforeMethod(): void
    {
    }
}
