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

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @requires extension nonExistingExtension
 */
final class RequirementsClassBeforeClassHookTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        throw new Exception(__METHOD__ . ' should not be called because of class requirements.');
    }
}
