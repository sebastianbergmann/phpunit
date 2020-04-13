<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class Issue3904Test extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}

final class Issue3904_3TestSuiteLoader
{
    public static function suite()
    {
        return new Issue3904Test();
    }
}
