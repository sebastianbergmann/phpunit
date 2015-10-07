<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputOption;

/**
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Option_Configuration extends PHPUnit_TextUI_Option_Option
{
    public function __construct()
    {
        parent::__construct(
            'configuration',
            'c',
            InputOption::VALUE_REQUIRED,
            'Read configuration from XML file.'
        );
    }

    /**
     * @param mixed $value
     *
     * @return array|mixed
     */
    public function convertValue($value)
    {
        // When "-d=key=value" or "-d key=value" requested.
        // The parsing do not removes '=' or ' '
        if ($value[0] === '=' || $value[0] === ' ') {
            $value = substr($value, 1, strlen($value));
        }

        return $value;
    }
}
