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
 * @requires extension bar
 * @requires extension foo >= 1.0.0
 */
final class RequiresPhpExtensionTest extends TestCase
{
    /**
     * @requires extension foo
     * @requires extension bar >= 1.0.0
     */
    public function testOne(): void
    {
    }

    /**
     * @requires extension baz < 2.0.0
     */
    public function testTwo(): void
    {
    }
}
