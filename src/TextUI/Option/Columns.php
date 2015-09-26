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
final class PHPUnit_TextUI_Option_Columns extends PHPUnit_TextUI_Option_Option
{
    public function __construct()
    {
        parent::__construct(
            'columns',
            null,
            InputOption::VALUE_REQUIRED,
            "Use Number of columns (<number> or 'max') to use for progress output."
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
            return null;
        }

        if ($value === 'max') {
            return 'max';
        }

        return intval($value);
    }
}
