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
abstract class PHPUnit_TextUI_Option_Option extends InputOption
{
    /**
     * Convert a value to another format supported by the option.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertValue($value)
    {
        return $value;
    }
}
