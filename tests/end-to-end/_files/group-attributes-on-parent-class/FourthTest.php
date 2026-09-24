<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\GroupAttributesOnParentClass;

require_once __DIR__ . '/AbstractParentTestCase.php';

final class FourthTest extends AbstractParentTestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
