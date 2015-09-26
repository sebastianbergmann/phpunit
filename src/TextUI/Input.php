<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\ArgvInput;

/**
 * @author Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 *
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Input extends ArgvInput
{
    /**
     * Returns the option value for a given option name.
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws \InvalidArgumentException When option given doesn't exist
     */
    public function getOption($name)
    {
        $value = parent::getOption($name);

        $option = $this->definition->getOption($name);
        // In case the option comes from the console component
        if ($option instanceof PHPUnit_TextUI_Option_Option) {
            /**
             * @var $option PHPUnit_TextUI_Option_Option
             */
            $value = $option->convertValue($value);
        }

        return $value;
    }
}
