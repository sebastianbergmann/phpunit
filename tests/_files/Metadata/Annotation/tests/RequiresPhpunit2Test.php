<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Annotation;

use PHPUnit\Framework\TestCase;

/**
 * @requires PHPUnit ^10.0
 */
final class RequiresPhpunit2Test extends TestCase
{
    /**
     * @requires PHPUnit ^11.0
     */
    public function testOne(): void
    {
    }
}
