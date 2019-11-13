<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use Composer\XdebugHandler\XdebugHandler;
use PHPUnit\TextUI\ResultPrinter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XdebugManager extends XdebugHandler
{
    /**
     * @var bool
     */
    private $noXdebug;

    public function __construct(array $arguments)
    {
        $this->noXdebug = $arguments['noXdebug'] ?? false;
        parent::__construct('phpunit', '--colors=' . ResultPrinter::COLOR_ALWAYS);

        // Use persistent restart settings if we restart
        $this->setPersistent();
    }

    protected function requiresRestart($isLoaded)
    {
        return $isLoaded && $this->noXdebug;
    }
}
