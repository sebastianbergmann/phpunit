<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace TestFixture\Issue6391;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/../src/Issue6391.php';

final class Issue6391Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Issue6391::$instance = new Issue6391;
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
