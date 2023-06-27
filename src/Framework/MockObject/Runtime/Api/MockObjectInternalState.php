<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockObjectInternalState
{
    /**
     * @var array<class-string, array<string, true>>
     */
    private static array $deprecationEmittedForTest = [];
    private object $originalObject;

    /**
     * @param class-string $class
     */
    public static function setDeprecationEmittedForTest(string $class, string $id): void
    {
        self::$deprecationEmittedForTest[$class][$id] = true;
    }

    /**
     * @param class-string $class
     */
    public static function issetDeprecationEmittedForTest(string $class, string $id): bool
    {
        return isset(self::$deprecationEmittedForTest[$class][$id]);
    }

    public function setOriginalObject(object $originalObject): void
    {
        $this->originalObject = $originalObject;
    }

    public function getOriginalObject(): ?object
    {
        return $this->originalObject;
    }
}
