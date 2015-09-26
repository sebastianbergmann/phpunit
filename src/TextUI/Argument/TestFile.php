<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputArgument;

/**
 * @author Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 *
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Argument_TestFile extends PHPUnit_TextUI_Argument_Argument
{
    public function __construct()
    {
        parent::__construct(
            'test-file',
            InputArgument::OPTIONAL,
            'The path to the test case file or directory.'
        );
    }
}
