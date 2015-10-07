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
final class PHPUnit_TextUI_Option_Repeat extends PHPUnit_TextUI_Option_Option
{
    public function __construct()
    {
        parent::__construct(
            'repeat',
            null,
            InputOption::VALUE_REQUIRED,
            'Runs the test(s) repeatedly.'
        );
    }

    /**
     * Convert a value to another format supported by the option.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertValue($value)
    {
        if (null === $value) {
            return $value;
        }

        return intval($value);
    }
}
