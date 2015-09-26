<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit_TextUI_Input as ArgvInput;

/**
 * @author Yannick Voyer <star.yvoyer@gmail.com> (http://github.com/yvoyer)
 *
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_InputDefinitionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_TextUI_InputDefinition
     */
    private $definition;

    public function setUp()
    {
        $this->definition = PHPUnit_TextUI_ConsoleInputDefinition::defaultDefinition();
    }

    public function test_it_should_support_colors_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_AUTO,
            $input->getOption('colors'),
            'Color should be default when unset'
        );

        $input = new ArgvInput(['command', '--colors'], $this->definition);
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_AUTO,
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
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getArgument('test'));

        $input = new ArgvInput(['command', 'SomeFile.php'], $this->definition);
        $this->assertSame('SomeFile.php', $input->getArgument('test'));
    }

    public function test_it_should_support_test_file_argument()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getArgument('test-file'));

        $input = new ArgvInput(['command', 'test.php'], $this->definition);
        $this->assertNull($input->getArgument('test-file'));

        $input = new ArgvInput(['command', 'test.php testFile.phpt'], $this->definition);
        $this->assertSame('testFile.phpt', $input->getArgument('test-file'));
    }

    public function test_it_should_support_coverage_clover_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('coverage-clover'));

        $input = new ArgvInput(['command', '--coverage-clover=file'], $this->definition);
        $this->assertSame('file', $input->getOption('coverage-clover'));
    }

    public function test_it_should_support_coverage_crap4j_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('coverage-crap4j'));

        $input = new ArgvInput(['command', '--coverage-crap4j=file'], $this->definition);
        $this->assertSame('file', $input->getOption('coverage-crap4j'));
    }

    public function test_it_should_support_coverage_html_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('coverage-html'));

        $input = new ArgvInput(['command', '--coverage-html=dir'], $this->definition);
        $this->assertSame('dir', $input->getOption('coverage-html'));
    }

    public function test_it_should_support_coverage_php_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('coverage-php'));

        $input = new ArgvInput(['command', '--coverage-php=file'], $this->definition);
        $this->assertSame('file', $input->getOption('coverage-php'));
    }

    public function test_it_should_support_coverage_text_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        // todo add constant for COVERAGE_TEXT_STANDARD_OUTPUT = 'php://stdout'
        $this->assertSame('php://stdout', $input->getOption('coverage-text'));

        $input = new ArgvInput(['command', '--coverage-text=format'], $this->definition);
        $this->assertSame('format', $input->getOption('coverage-text'));
    }

    public function test_it_should_support_coverage_xml_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('coverage-xml'));

        $input = new ArgvInput(['command', '--coverage-xml=dir'], $this->definition);
        $this->assertSame('dir', $input->getOption('coverage-xml'));
    }

    public function test_it_should_support_log_junit_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('log-junit'));

        $input = new ArgvInput(['command', '--log-junit=file'], $this->definition);
        $this->assertSame('file', $input->getOption('log-junit'));
    }

    public function test_it_should_support_log_tap_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('log-tap'));

        $input = new ArgvInput(['command', '--log-tap=file'], $this->definition);
        $this->assertSame('file', $input->getOption('log-tap'));
    }

    public function test_it_should_support_log_json_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('log-json'));

        $input = new ArgvInput(['command', '--log-json=file'], $this->definition);
        $this->assertSame('file', $input->getOption('log-json'));
    }

    public function test_it_should_support_testdox_html_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('testdox-html'));

        $input = new ArgvInput(['command', '--testdox-html=file'], $this->definition);
        $this->assertSame('file', $input->getOption('testdox-html'));
    }

    public function test_it_should_support_testdox_text_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('testdox-text'));

        $input = new ArgvInput(['command', '--testdox-text=file'], $this->definition);
        $this->assertSame('file', $input->getOption('testdox-text'));
    }

    public function test_it_should_support_filter_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('filter'));

        $input = new ArgvInput(['command', '--filter=pattern'], $this->definition);
        $this->assertSame('pattern', $input->getOption('filter'));
    }

    public function test_it_should_support_testsuite_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('testsuite'));

        $input = new ArgvInput(['command', '--testsuite=pattern'], $this->definition);
        $this->assertSame('pattern', $input->getOption('testsuite'));
    }

    public function test_it_should_support_group_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertSame([], $input->getOption('group'));

        $input = new ArgvInput(['command', '--group=group1,group2'], $this->definition);
        $this->assertSame(['group1','group2'], $input->getOption('group'));
    }

    public function test_it_should_support_exclude_group_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertSame([], $input->getOption('exclude-group'));

        $input = new ArgvInput(['command', '--exclude-group=group1,group2'], $this->definition);
        $this->assertSame(['group1','group2'], $input->getOption('exclude-group'));
    }

    public function test_it_should_support_test_suffix_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertSame(['Test.php','.phpt'], $input->getOption('test-suffix'), 'default value is not as expected');

        $input = new ArgvInput(['command', '--test-suffix=Test.php'], $this->definition);
        $this->assertSame(['Test.php'], $input->getOption('test-suffix'));
    }

    /**
     * @dataProvider provideTestExecutionOptions
     */
    public function test_it_should_support_boolean_option($option)
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertFalse($input->getOption($option), 'Should be false when the option is not set');

        $input = new ArgvInput(['command', '--' . $option], $this->definition);
        $this->assertTrue($input->getOption($option), 'Should be true when the option is set');
    }

    public function provideTestExecutionOptions()
    {
        return [
            ['list-groups'],
            ['report-useless-tests'],
            ['strict-coverage'],
            ['strict-global-state'],
            ['disallow-test-output'],
            ['enforce-time-limit'],
            ['disallow-todo-tests'],
            ['process-isolation'],
            ['no-globals-backup'],
            ['static-backup'],
            ['stderr'],
            ['stop-on-error'],
            ['stop-on-failure'],
            ['stop-on-risky'],
            ['stop-on-skipped'],
            ['stop-on-incomplete'],
            ['debug'],
            ['tap'],
            ['testdox'],
            ['no-configuration'],
            ['no-coverage'],
            ['version'],
        ];
    }

    public function test_it_should_support_columns_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('columns'));

        $input = new ArgvInput(['command', '--columns=123'], $this->definition);
        $this->assertSame(123, $input->getOption('columns'));

        $input = new ArgvInput(['command', '--columns=max'], $this->definition);
        $this->assertSame('max', $input->getOption('columns'));
    }

    public function test_it_should_support_verbose_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertFalse($input->getOption('verbose'));

        $input = new ArgvInput(['command', '--verbose'], $this->definition);
        $this->assertTrue($input->getOption('verbose'));

        $input = new ArgvInput(['command', '-v'], $this->definition);
        $this->assertTrue($input->getOption('verbose'));
    }

    public function test_it_should_support_loader_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('loader'));

        $input = new ArgvInput(['command', '--loader=loader'], $this->definition);
        $this->assertSame('loader', $input->getOption('loader'));
    }

    public function test_it_should_support_repeat_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('repeat'));

        $input = new ArgvInput(['command', '--repeat=123'], $this->definition);
        $this->assertSame(123, $input->getOption('repeat'));
    }

    public function test_it_should_support_printer_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('printer'));

        $input = new ArgvInput(['command', '--printer=testListener'], $this->definition);
        $this->assertSame('testListener', $input->getOption('printer'));
    }

    public function test_it_should_support_bootstrap_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('bootstrap'));

        $input = new ArgvInput(['command', '--bootstrap=file'], $this->definition);
        $this->assertSame('file', $input->getOption('bootstrap'));
    }

    public function test_it_should_support_configuration_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('configuration'));

        $input = new ArgvInput(['command', '--configuration=file'], $this->definition);
        $this->assertSame('file', $input->getOption('configuration'));

        $input = new ArgvInput(['command', '-c=file'], $this->definition);
        $this->assertSame('file', $input->getOption('configuration'));
    }

    public function test_it_should_support_include_path_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertSame([], $input->getOption('include-path'));

        $input = new ArgvInput(['command', '--include-path=path1,path2'], $this->definition);
        $this->assertSame(['path1', 'path2'], $input->getOption('include-path'));
    }

    public function test_it_should_support_d_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertNull($input->getOption('ini-set'));

        $input = new ArgvInput(['command', '--ini-set=key'], $this->definition);
        $this->assertSame(['key'], $input->getOption('ini-set'));

        $input = new ArgvInput(['command', '-d key=value'], $this->definition);
        $this->assertSame(['key', 'value'], $input->getOption('ini-set'));
    }

    public function test_it_should_support_help_option()
    {
        $input = new ArgvInput(['command'], $this->definition);
        $this->assertFalse($input->getOption('help'));

        $input = new ArgvInput(['command', '--help'], $this->definition);
        $this->assertTrue($input->getOption('help'));

        $input = new ArgvInput(['command', '-h'], $this->definition);
        $this->assertTrue($input->getOption('help'));
    }
}
