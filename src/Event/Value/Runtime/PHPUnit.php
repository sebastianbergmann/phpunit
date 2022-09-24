<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Runtime;

use PHPUnit\Runner\Version;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class PHPUnit
{
    private readonly string $version;
    private readonly string $series;

    public function __construct()
    {
        $this->version = Version::id();
        $this->series  = Version::series();
    }

    public function version(): string
    {
        return $this->version;
    }

    public function series(): string
    {
        return $this->series;
    }
}
