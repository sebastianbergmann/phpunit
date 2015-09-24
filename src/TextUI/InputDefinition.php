<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 *
 * @since Class available since Release 5.0.0
 *
 * Definition of all options and arguments supported by PHPUnit
 */
class PHPUnit_TextUI_InputDefinition extends InputDefinition
{
    public function __construct()
    {
        $default = [
            $this->colorOption(),
            $this->testArgument(),
        ];

        parent::__construct($default);
    }

    /**
     * @return InputOption
     */
    private function colorOption()
    {
        return new InputOption(
            'colors',
            null,
            InputOption::VALUE_OPTIONAL,
            'Use colors in output ("never", "auto" or "always").',
            PHPUnit_TextUI_ResultPrinter::COLOR_DEFAULT
        );
    }

    private function testArgument()
    {
        return new InputArgument(
            'test',
            InputArgument::OPTIONAL,
            'The path to the test case file or directory.'
        );
    }
}
