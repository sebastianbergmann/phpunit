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
 * @author Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 *
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Option_IniSet extends PHPUnit_TextUI_Option_Option
{
    public function __construct()
    {
        parent::__construct(
            'ini-set',
            'd',
            InputOption::VALUE_REQUIRED,
            'Sets a php.ini value.',
            []
        );
    }

    /**
     * @param mixed $value
     *
     * @return array|mixed
     */
    public function convertValue($value)
    {
        if (empty($value)) {
            return null;
        }

        // When "-d=key=value" or "-d key=value" requested.
        // The parsing do not removes '=' or ' '
        if ($value[0] === '=' || $value[0] === ' ') {
            $value = substr($value, 1, strlen($value));
        }

        return explode('=', $value);
    }
}
