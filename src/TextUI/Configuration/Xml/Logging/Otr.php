<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\Logging;

use PHPUnit\TextUI\Configuration\File;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Otr
{
    private File $target;
    private bool $includeGitInformation;

    public function __construct(File $target, bool $includeGitInformation)
    {
        $this->target                = $target;
        $this->includeGitInformation = $includeGitInformation;
    }

    public function target(): File
    {
        return $this->target;
    }

    public function includeGitInformation(): bool
    {
        return $this->includeGitInformation;
    }
}
