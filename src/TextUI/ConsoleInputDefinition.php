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

/**
 * @since Class available since Release 6.0.0
 *
 * Definition of all options and arguments supported by PHPUnit
 */
class PHPUnit_TextUI_ConsoleInputDefinition extends InputDefinition implements PHPUnit_TextUI_InputDefinition
{
    /**
     * @param PHPUnit_TextUI_Argument_Argument[]|PHPUnit_TextUI_Option_Option[] $definitions
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct(array $definitions = [])
    {
        foreach ($definitions as $input) {
            if ($input instanceof PHPUnit_TextUI_Argument_Argument) {
                $this->registerArgument($input);
            } else if ($input instanceof PHPUnit_TextUI_Option_Option) {
                $this->registerOption($input);
            } else {
                $class = get_class($input);
                throw new PHPUnit_Framework_Exception("Invalid definition given '{$class}'.");
            }
        }
    }

    /**
     * @param PHPUnit_TextUI_Argument_Argument $argument
     */
    public function registerArgument(PHPUnit_TextUI_Argument_Argument $argument)
    {
        $this->addArgument($argument);
    }

    /**
     * @param PHPUnit_TextUI_Option_Option $option
     */
    public function registerOption(PHPUnit_TextUI_Option_Option $option)
    {
        $this->addOption($option);
    }

    /**
     * @return PHPUnit_TextUI_ConsoleInputDefinition
     */
    public static function defaultDefinition()
    {
        $default = [
            new PHPUnit_TextUI_Argument_Test(),
            new PHPUnit_TextUI_Argument_TestFile(),

            // todo group coverage option in a CoverCommand ?
            new PHPUnit_TextUI_Option_CoverageClover(),
            new PHPUnit_TextUI_Option_CoverageCrap4j(),
            new PHPUnit_TextUI_Option_CoverageHtml(),
            new PHPUnit_TextUI_Option_CoveragePHP(),
            new PHPUnit_TextUI_Option_CoverageText(),
            new PHPUnit_TextUI_Option_CoverageXml(),
            new PHPUnit_TextUI_Option_StrictCoverage(),

            // todo Group log options in a LogCommand ?
            new PHPUnit_TextUI_Option_LogJUnit(),
            new PHPUnit_TextUI_Option_LogTap(),
            new PHPUnit_TextUI_Option_LogJSON(),

            // todo Group testdox option in a TestdoxCommand ?
            new PHPUnit_TextUI_Option_TestdoxHtml(),
            new PHPUnit_TextUI_Option_TestdoxText(),

            // todo Group group option in a GroupCommand ?
            new PHPUnit_TextUI_Option_Group(),
            new PHPUnit_TextUI_Option_ExcludeGroup(),
            new PHPUnit_TextUI_Option_ListGroups(),

            new PHPUnit_TextUI_Option_Colors(),
            new PHPUnit_TextUI_Option_Filter(),
            new PHPUnit_TextUI_Option_TestSuite(),
            new PHPUnit_TextUI_Option_TestSuffix(),
            new PHPUnit_TextUI_Option_ReportUselessTests(),
            new PHPUnit_TextUI_Option_StrictGlobalState(),
            new PHPUnit_TextUI_Option_DisallowTestOutput(),
            new PHPUnit_TextUI_Option_EnforceTimeLimit(),
            new PHPUnit_TextUI_Option_DisallowTodoTests(),
            new PHPUnit_TextUI_Option_ProcessIsolation(),
            new PHPUnit_TextUI_Option_NoGlobalsBackup(),
            new PHPUnit_TextUI_Option_StaticBackup(),
            new PHPUnit_TextUI_Option_Stderr(),
            new PHPUnit_TextUI_Option_StopOnError(),
            new PHPUnit_TextUI_Option_StopOnFailure(),
            new PHPUnit_TextUI_Option_StopOnRisky(),
            new PHPUnit_TextUI_Option_StopOnSkipped(),
            new PHPUnit_TextUI_Option_StopOnIncomplete(),
            new PHPUnit_TextUI_Option_Verbose(),
            new PHPUnit_TextUI_Option_Debug(),
            new PHPUnit_TextUI_Option_Tap(),
            new PHPUnit_TextUI_Option_Testdox(),
            new PHPUnit_TextUI_Option_NoConfiguration(),
            new PHPUnit_TextUI_Option_NoCoverage(),
            new PHPUnit_TextUI_Option_Version(),
            new PHPUnit_TextUI_Option_Columns(),
            new PHPUnit_TextUI_Option_Loader(),
            new PHPUnit_TextUI_Option_Repeat(),
            new PHPUnit_TextUI_Option_Printer(),
            new PHPUnit_TextUI_Option_Bootstrap(),
            new PHPUnit_TextUI_Option_Configuration(),
            new PHPUnit_TextUI_Option_IncludePath(),
            new PHPUnit_TextUI_Option_IniSet(),
            new PHPUnit_TextUI_Option_Help(),
            new PHPUnit_TextUI_Option_Strict(),
            new PHPUnit_TextUI_Option_CheckVersion(),
            new PHPUnit_TextUI_Option_SelfUpdate(),
        ];

        return new self($default);
    }
}
