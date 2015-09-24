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
 */
final class PHPUnit_TextUI_InputDefinitionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_TextUI_InputDefinition
     */
    private $definition;

    public function setUp()
    {
        $this->definition = new PHPUnit_TextUI_InputDefinition();
    }

    public function test_it_should_support_colors_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_DEFAULT,
            $input->getOption('colors'),
            'Color should be default when unset'
        );

        $input = new ArgvInput(['command', '--colors'], $this->definition);
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_DEFAULT,
            $input->getOption('colors'),
            'Colors should be default when option option present but no value given'
        );

        $input = new ArgvInput(
            ['command', '--colors=' . PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS],
            $this->definition
        );
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS,
            $input->getOption('colors'),
            'Colors should be defined to given value'
        );
    }

    public function test_it_should_support_test_argument()
    {
        $input = new ArgvInput(['command', 'SomeFile.php'], $this->definition);
        $this->assertSame('SomeFile.php', $input->getArgument('test'));
    }
}
