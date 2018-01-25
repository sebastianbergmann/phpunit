<?php
use PHPUnit\Framework\TestCase;

/**
 * @requires extension nonExistingExtension
 */
class RequirementsClassBeforeClassHookTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        throw new Exception(__METHOD__ . ' should not be called because of class requirements.');
    }
}
