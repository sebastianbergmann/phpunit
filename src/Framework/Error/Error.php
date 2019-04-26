<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Error;

use PHPUnit\Framework\Exception;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class Error extends Exception
{

    private $severity;

    public function __construct(string $message, int $code, int $severity, string $file, int $line, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->severity = $severity;
        $this->file = $file;
        $this->line = $line;
    }

    public function getSeverity() {
        return $this->severity;
    }


}
