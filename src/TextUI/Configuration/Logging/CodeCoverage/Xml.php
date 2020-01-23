<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration\Logging\CodeCoverage;

use PHPUnit\TextUI\Configuration\Directory;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Xml
{
    /**
     * @var Directory
     */
    private $target;

    public function __construct(Directory $target)
    {
        $this->target = $target;
    }

    public function target(): Directory
    {
        return $this->target;
    }
}
