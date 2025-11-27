<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report;

use PHPUnit\TextUI\Configuration\Directory;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Xml
{
    private Directory $target;
    private bool $includeSource;

    public function __construct(Directory $target, bool $includeSource)
    {
        $this->target        = $target;
        $this->includeSource = $includeSource;
    }

    public function target(): Directory
    {
        return $this->target;
    }

    public function includeSource(): bool
    {
        return $this->includeSource;
    }
}
