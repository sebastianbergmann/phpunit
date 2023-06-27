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

use WeakMap;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class InternalMockObjectState
{
    private static ?self $instance = null;

    /**
     * @var array<class-string, array<string, true>>
     */
    private array $deprecationEmittedForTest;

    /**
     * @var WeakMap<object, object>
     */
    private WeakMap $originalObject;

    public static function getInstance(): self
    {
        return self::$instance ?? self::$instance = new self;
    }

    private function __construct()
    {
        $this->deprecationEmittedForTest = [];
        $this->originalObject            = new WeakMap;
    }

    /**
     * @param class-string $class
     */
    public function setDeprecationEmittedForTest(string $class, string $id): void
    {
        $this->deprecationEmittedForTest[$class][$id] = true;
    }

    /**
     * @param class-string $class
     */
    public function issetDeprecationEmittedForTest(string $class, string $id): bool
    {
        return isset($this->deprecationEmittedForTest[$class][$id]);
    }

    public function setOriginalObject(object $mock, object $originalObject): void
    {
        $this->originalObject[$mock] = $originalObject;
    }

    public function getOriginalObject(object $mock): ?object
    {
        return $this->originalObject[$mock];
    }
}
