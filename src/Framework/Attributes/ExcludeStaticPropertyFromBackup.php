<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class ExcludeStaticPropertyFromBackup
{
    /**
     * @psalm-var class-string
     */
    private string $className;
    private string $propertyName;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $propertyName)
    {
        $this->className    = $className;
        $this->propertyName = $propertyName;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function propertyName(): string
    {
        return $this->propertyName;
    }
}
