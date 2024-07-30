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
final readonly class External
{
    private string $target;

    /**
     * The target parameter is expected to be a fully qualifiy class name implementing the ExternalLogger
     * interface.
     *
     * @param string $target The external logger class name
     *
     * @see \PHPUnit\Logging\ExternalLogger
     */
    public function __construct(string $target)
    {
        $this->target = $target;
    }

    public function target(): string
    {
        return $this->target;
    }
}
