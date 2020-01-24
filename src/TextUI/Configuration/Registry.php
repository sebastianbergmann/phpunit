<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Registry
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var Configuration[]
     */
    private $configurations = [];

    public static function getInstance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function get(string $filename): Configuration
    {
        if (!isset($this->configurations[$filename])) {
            $this->configurations[$filename] = (new Loader)->load($filename);
        }

        return $this->configurations[$filename];
    }
}
