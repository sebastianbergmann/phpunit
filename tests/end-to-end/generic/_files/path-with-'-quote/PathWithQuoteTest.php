<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function defined;
use PHPUnit\Framework\TestCase;

final class PathWithQuoteTest extends TestCase
{
    public function testBootstrapScriptWasLoaded(): void
    {
        $this->assertTrue(defined('PHPUNIT_TEST_FIXTURE_PATH_WITH_QUOTE_BOOTSTRAP_LOADED'));
    }
}
