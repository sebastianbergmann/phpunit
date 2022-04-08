<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use PHPUnit\TextUI\Help;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ShowHelpCommand implements Command
{
    private bool $success;

    public function __construct(bool $success)
    {
        $this->success = $success;
    }

    public function execute(): Result
    {
        return Result::from(
            (new Help)->generate(),
            $this->success
        );
    }
}
