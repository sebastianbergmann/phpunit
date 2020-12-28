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

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresPhpExtension
{
    private string $extension;

    private string $version;

    /**
     * @psalm-var '<'|'lt'|'<='|'le'|'>'|'gt'|'>='|'ge'|'=='|'='|'eq'|'!='|'<>'|'ne'
     */
    private string $operator;

    /**
     * @psalm-param '<'|'lt'|'<='|'le'|'>'|'gt'|'>='|'ge'|'=='|'='|'eq'|'!='|'<>'|'ne' $operator
     */
    public function __construct(string $extension, string $version, string $operator = '>=')
    {
        $this->extension = $extension;
        $this->version   = $version;
        $this->operator  = $operator;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function version(): string
    {
        return $this->version;
    }

    /**
     * @psalm-return '<'|'lt'|'<='|'le'|'>'|'gt'|'>='|'ge'|'=='|'='|'eq'|'!='|'<>'|'ne'
     */
    public function operator(): string
    {
        return $this->operator;
    }
}
