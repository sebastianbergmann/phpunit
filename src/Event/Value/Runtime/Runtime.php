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

use function get_current_user;
use function gethostname;
use function sprintf;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Runtime
{
    private OperatingSystem $operatingSystem;
    private PHP $php;
    private PHPUnit $phpunit;
    private string $hostName;
    private string $userName;

    public function __construct()
    {
        $this->operatingSystem = new OperatingSystem;
        $this->php             = new PHP;
        $this->phpunit         = new PHPUnit;
        $this->hostName        = gethostname();
        $this->userName        = get_current_user();
    }

    public function asString(): string
    {
        $php = $this->php();

        return sprintf(
            'PHPUnit %s using PHP %s (%s) on %s',
            $this->phpunit()->versionId(),
            $php->version(),
            $php->sapi(),
            $this->operatingSystem()->operatingSystem(),
        );
    }

    public function operatingSystem(): OperatingSystem
    {
        return $this->operatingSystem;
    }

    public function php(): PHP
    {
        return $this->php;
    }

    public function phpunit(): PHPUnit
    {
        return $this->phpunit;
    }

    public function hostName(): string
    {
        return $this->hostName;
    }

    public function userName(): string
    {
        return $this->userName;
    }
}
