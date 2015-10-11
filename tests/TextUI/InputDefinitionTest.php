<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_InputDefinitionTest extends PHPUnit_Framework_TestCase
{
    public function test_it_should_support_colors_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_AUTO,
            $input->getOption('colors'),
            'Color should be default when unset'
        );

        $input = new PHPUnit_TextUI_Input(['command', '--colors']);
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_AUTO,
            $input->getOption('colors'),
            'Colors should be default when option option present but no value given'
        );

        $input = new PHPUnit_TextUI_Input(
            ['command', '--colors=' . PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS]
        );
        $this->assertSame(
            PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS,
            $input->getOption('colors'),
            'Colors should be defined to given value'
        );
    }

    public function test_it_should_support_test_argument()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getArgument('test'));

        $input = new PHPUnit_TextUI_Input(['command', 'SomeFile.php']);
        $this->assertSame('SomeFile.php', $input->getArgument('test'));
    }

    public function test_it_should_support_test_file_argument()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getArgument('test-file'));

        $input = new PHPUnit_TextUI_Input(['command', 'test.php']);
        $this->assertNull($input->getArgument('test-file'));

        $input = new PHPUnit_TextUI_Input(['command', 'test.php testFile.phpt']);
        $this->assertSame('testFile.phpt', $input->getArgument('test-file'));
    }

    public function test_it_should_support_coverage_clover_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('coverage-clover'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-clover=file']);
        $this->assertSame('file', $input->getOption('coverage-clover'));
    }

    public function test_it_should_support_coverage_crap4j_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('coverage-crap4j'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-crap4j=file']);
        $this->assertSame('file', $input->getOption('coverage-crap4j'));
    }

    public function test_it_should_support_coverage_html_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('coverage-html'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-html=dir']);
        $this->assertSame('dir', $input->getOption('coverage-html'));
    }

    public function test_it_should_support_coverage_php_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('coverage-php'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-php=file']);
        $this->assertSame('file', $input->getOption('coverage-php'));
    }

    /**
     * This use case is difficult to implement without introducing a BC break,
     * since the feature is currently being developed
     * @see https://github.com/symfony/symfony/pull/12773
     */
    public function test_it_should_support_coverage_text_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('coverage-text'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-text']);
        $this->assertSame('php://stdout', $input->getOption('coverage-text'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-text=format']);
        $this->assertSame('format', $input->getOption('coverage-text'));
    }

    public function test_it_should_support_coverage_xml_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('coverage-xml'));

        $input = new PHPUnit_TextUI_Input(['command', '--coverage-xml=dir']);
        $this->assertSame('dir', $input->getOption('coverage-xml'));
    }

    public function test_it_should_support_log_junit_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('log-junit'));

        $input = new PHPUnit_TextUI_Input(['command', '--log-junit=file']);
        $this->assertSame('file', $input->getOption('log-junit'));
    }

    public function test_it_should_support_log_tap_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('log-tap'));

        $input = new PHPUnit_TextUI_Input(['command', '--log-tap=file']);
        $this->assertSame('file', $input->getOption('log-tap'));
    }

    public function test_it_should_support_log_json_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('log-json'));

        $input = new PHPUnit_TextUI_Input(['command', '--log-json=file']);
        $this->assertSame('file', $input->getOption('log-json'));
    }

    public function test_it_should_support_testdox_html_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('testdox-html'));

        $input = new PHPUnit_TextUI_Input(['command', '--testdox-html=file']);
        $this->assertSame('file', $input->getOption('testdox-html'));
    }

    public function test_it_should_support_testdox_text_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('testdox-text'));

        $input = new PHPUnit_TextUI_Input(['command', '--testdox-text=file']);
        $this->assertSame('file', $input->getOption('testdox-text'));
    }

    public function test_it_should_support_filter_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('filter'));

        $input = new PHPUnit_TextUI_Input(['command', '--filter=pattern']);
        $this->assertSame('pattern', $input->getOption('filter'));
    }

    public function test_it_should_support_testsuite_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('testsuite'));

        $input = new PHPUnit_TextUI_Input(['command', '--testsuite=pattern']);
        $this->assertSame('pattern', $input->getOption('testsuite'));
    }

    public function test_it_should_support_group_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertSame([], $input->getOption('group'));

        $input = new PHPUnit_TextUI_Input(['command', '--group=group1,group2']);
        $this->assertSame(['group1','group2'], $input->getOption('group'));
    }

    public function test_it_should_support_exclude_group_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertSame([], $input->getOption('exclude-group'));

        $input = new PHPUnit_TextUI_Input(['command', '--exclude-group=group1,group2']);
        $this->assertSame(['group1','group2'], $input->getOption('exclude-group'));
    }

    public function test_it_should_support_test_suffix_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertSame(['Test.php','.phpt'], $input->getOption('test-suffix'), 'default value is not as expected');

        $input = new PHPUnit_TextUI_Input(['command', '--test-suffix=Test.php']);
        $this->assertSame(['Test.php'], $input->getOption('test-suffix'));
    }

    /**
     * @dataProvider provideTestExecutionOptions
     */
    public function test_it_should_support_boolean_option($option)
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertFalse($input->getOption($option), 'Should be false when the option is not set');

        $input = new PHPUnit_TextUI_Input(['command', '--' . $option]);
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
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('columns'));

        $input = new PHPUnit_TextUI_Input(['command', '--columns=123']);
        $this->assertSame(123, $input->getOption('columns'));

        $input = new PHPUnit_TextUI_Input(['command', '--columns=max']);
        $this->assertSame('max', $input->getOption('columns'));
    }

    public function test_it_should_support_verbose_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertFalse($input->getOption('verbose'));

        $input = new PHPUnit_TextUI_Input(['command', '--verbose']);
        $this->assertTrue($input->getOption('verbose'));

        $input = new PHPUnit_TextUI_Input(['command', '-v']);
        $this->assertTrue($input->getOption('verbose'));
    }

    public function test_it_should_support_loader_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('loader'));

        $input = new PHPUnit_TextUI_Input(['command', '--loader=loader']);
        $this->assertSame('loader', $input->getOption('loader'));
    }

    public function test_it_should_support_repeat_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('repeat'));

        $input = new PHPUnit_TextUI_Input(['command', '--repeat=123']);
        $this->assertSame(123, $input->getOption('repeat'));
    }

    public function test_it_should_support_printer_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('printer'));

        $input = new PHPUnit_TextUI_Input(['command', '--printer=testListener']);
        $this->assertSame('testListener', $input->getOption('printer'));
    }

    public function test_it_should_support_bootstrap_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('bootstrap'));

        $input = new PHPUnit_TextUI_Input(['command', '--bootstrap=file']);
        $this->assertSame('file', $input->getOption('bootstrap'));
    }

    public function test_it_should_support_configuration_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('configuration'));

        $input = new PHPUnit_TextUI_Input(['command', '--configuration=file']);
        $this->assertSame('file', $input->getOption('configuration'));

        $input = new PHPUnit_TextUI_Input(['command', '-c=file']);
        $this->assertSame('file', $input->getOption('configuration'));
    }

    public function test_it_should_support_include_path_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertSame([], $input->getOption('include-path'));

        $input = new PHPUnit_TextUI_Input(['command', '--include-path=path1,path2']);
        $this->assertSame(['path1', 'path2'], $input->getOption('include-path'));
    }

    public function test_it_should_support_d_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertNull($input->getOption('ini-set'));

        $input = new PHPUnit_TextUI_Input(['command', '--ini-set=key']);
        $this->assertSame(['key'], $input->getOption('ini-set'));

        $input = new PHPUnit_TextUI_Input(['command', '-d key=value']);
        $this->assertSame(['key', 'value'], $input->getOption('ini-set'));
    }

    public function test_it_should_support_help_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertFalse($input->getOption('help'));

        $input = new PHPUnit_TextUI_Input(['command', '--help']);
        $this->assertTrue($input->getOption('help'));

        $input = new PHPUnit_TextUI_Input(['command', '-h']);
        $this->assertTrue($input->getOption('help'));
    }

    /**
     * @depends test_it_should_support_verbose_option
     * @depends test_it_should_support_boolean_option
     * @depends test_it_should_support_configuration_option
     */
    public function test_it_should_resolve_ambiguous_option_with_no_value()
    {
        $input = new PHPUnit_TextUI_Input(['command', '--deb', '--verb']);
        $this->assertTrue($input->getOption('debug'), 'Should resolve to the debug option.');
        $this->assertTrue($input->getOption('verbose'), 'Should resolve to the verbose option.');
    }

    public function test_it_should_resolve_ambiguous_option_with_value()
    {
        $input = new PHPUnit_TextUI_Input(['command', '--conf=file']);
        $this->assertSame('file', $input->getOption('configuration'), 'Should resolve to the configuration option.');
    }

    public function test_it_should_support_strict_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertFalse($input->getOption('strict'));

        $input = new PHPUnit_TextUI_Input(['command', '--strict']);
        $this->assertTrue($input->getOption('strict'));
    }

    public function test_it_should_support_check_version_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertFalse($input->getOption('check-version'));

        $input = new PHPUnit_TextUI_Input(['command', '--check-version']);
        $this->assertTrue($input->getOption('check-version'));
    }

    public function test_it_should_support_self_update_option()
    {
        $input = new PHPUnit_TextUI_Input(['command']);
        $this->assertFalse($input->getOption('self-update'));

        $input = new PHPUnit_TextUI_Input(['command', '--self-update']);
        $this->assertTrue($input->getOption('self-update'));
    }
}
