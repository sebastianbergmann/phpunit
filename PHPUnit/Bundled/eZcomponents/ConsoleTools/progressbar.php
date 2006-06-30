<?php
/**
 * File containing the ezcConsoleProgressbar class.
 *
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Creating and maintaining progress-bars to be printed to the console. 
 *
 * <code>
 * $out = new ezcConsoleOutput();
 * 
 * // Create progress bar itself
 * $progress = new ezcConsoleProgressbar( $out, 100, array( 'step' => 5 ) );
 * 
 * $progress->options->emptyChar = '-';
 * $progress->options->progressChar = '#';
 * $progress->options->formatString = "Uploading file </tmp/foobar.tar.bz2>: %act%/%max% kb [%bar%]";
 * 
 * // Perform actions
 * $i = 0;
 * while ( $i++ < 20 ) 
 * {
 *     // Do whatever you want to indicate progress for
 *     usleep( mt_rand( 20000, 2000000 ) );
 *     // Advance the progressbar by one step ( uploading 5k per run )
 *     $progress->advance();
 * }
 * 
 * // Finish progress bar and jump to next line.
 * $progress->finish();
 * 
 * $out->outputText( "Successfully uploaded </tmp/foobar.tar.bz2>.\n", 'success' );
 * </code>
 *  
 * 
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleProgressbar
{
    /**
     * Settings for the progress bar.
     * Contains settings for the progress bar. Mandatory setting values are:
     *
     * <code>
     * $progress->max;      // The maximum progress value to reach.
     * </code>
     * 
     * @var array(string=>int)
     */
    protected $settings;

    /**
     * Options
     *
     * @var ezcConsoleProgressbarOptions
     */
    protected $options;

    /**
     * Storage for actual values to be replaced in the format string.
     * Actual values are stored here and will be inserted into the bar
     * before printing it.
     * 
     * @var array(string => string)
     */
    protected $valueMap = array( 
        'bar'       => '',
        'fraction'  => '',
        'act'       => '',
        'max'       => '',
    );

    /**
     * Stores the bar utilization.
     *
     * This array saves how much space a specific part of the bar utilizes to not
     * recalculate those on every step.
     * 
     * @var array(string => int)
     */
    protected $measures = array( 
        'barSpace'          => 0,
        'fractionSpace'     => 0,
        'actSpace'          => 0,
        'maxSpace'          => 0,
        'fixedCharSpace'    => 0,
    );

    /**
     * The current step the progress bar should show. 
     * 
     * @var int
     */
    protected $currentStep = 0;

    /**
     * The maximum number of steps to go.
     * Calculated once from the settings.
     *
     * @var int
     */
    protected $numSteps = 0;

    /**
     * The ezcConsoleOutput object to use.
     *
     * @var ezcConsoleOutput
     */
    protected $output;

    /**
     * Indicates if the starting point for the bar has been stored.
     * Per default this is false to indicate that no start position has been
     * stored, yet.
     * 
     * @var bool
     */
    protected $started = false;

    /**
     * Creates a new progress bar.
     *
     * @param ezcConsoleOutput $outHandler   Handler to utilize for output
     * @param int $max                       Maximum value, where progressbar 
     *                                       reaches 100%.
     * @param array(string=>string) $options Options
     *
     * @see ezcConsoleProgressbar::$settings
     * @see ezcConsoleProgressbar::$options
     */
    public function __construct( ezcConsoleOutput $outHandler, $max, array $options = array() )
    {
        $this->output = $outHandler;
        $this->__set( 'max', $max );
        $this->options = new ezcConsoleProgressbarOptions( $options );
    }
    
    /**
     * Set new options.
     * This method allows you to change the options of progressbar.
     *  
     * @param ezcConsoleProgresbarOptions $options The options to set.
     *
     * @throws ezcBaseSettingNotFoundException
     *         If you tried to set a non-existent option value.
     * @throws ezcBaseSettingValueException
     *         If the value is not valid for the desired option.
     * @throws ezcBaseValueException
     *         If you submit neither an array nor an instance of 
     *         ezcConsoleProgresbarOptions.
     */
    public function setOptions( $options ) 
    {
        if ( is_array( $options ) ) 
        {
            $this->options->merge( $options );
        } 
        else if ( $options instanceof ezcConsoleProgressbarOptions ) 
        {
            $this->options = $options;
        }
        else
        {
            throw new ezcBaseValueException( "options", $options, "instance of ezcConsoleProgressbarOptions" );
        }
    }

    /**
     * Returns the current options.
     * Returns the options currently set for this progressbar.
     * 
     * @return ezcConsoleProgressbarOptions The current options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Property read access.
     * 
     * @param string $key Name of the property.
     * @return mixed Value of the property or null.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the the desired property is not found.
     */
    public function __get( $key )
    {
        switch ( $key )
        {
            case 'options':
                return $this->options;
            case 'step':
                // Step is now an option
                return $this->options->step;
            case 'max':
                return $this->settings[$key];
            default:
                break;
        }
        throw new ezcBasePropertyNotFoundException( $key );
    }

    /**
     * Property write access.
     * 
     * @param string $key Name of the property.
     * @param mixed $val  The value for the property.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If a desired property could not be found.
     * @throws ezcBaseValueException
     *         If a desired property value is out of range.
     * @return void
     */
    public function __set( $key, $val )
    {
        switch ( $key )
        {
            case 'options':
                if ( !( $val instanceof ezcConsoleProgressbarOptions ) )
                {
                    throw new ezcBaseValueException( 'options',  $val, 'instance of ezcConsoleProgressbarOptions' );
                };
                break;
            case 'max':
                if ( ( !is_int( $val ) && !is_float( $val ) ) || $val < 0 )
                {
                    throw new ezcBaseValueException( $key, $val, 'number >= 0' );
                }
                break;
            case 'step':
                if ( ( !is_int( $val ) && !is_float( $val ) ) || $val < 0 )
                {
                    throw new ezcBaseValueException( $key, $val, 'number >= 0' );
                }
                // Step is now an option.
                $this->options->step = $val;
                return;
            default:
                throw new ezcBasePropertyNotFoundException( $key );
                break;
        }
        // Changes settings or options, need for recalculating measures
        $this->started = false;
        $this->$key = $val;
    }
 
    /**
     * Property isset access.
     * 
     * @param string $key Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $key )
    {
        switch ( $key )
        {
            case 'options':
            case 'max':
            case 'step':
                return true;
        }
        return false;
    }

    /**
     * Start the progress bar
     * Starts the progress bar and sticks it to the current line.
     * No output will be done yet. Call {@link ezcConsoleProgressbar::output()}
     * to print the bar.
     * 
     * @return void
     */
    public function start() 
    {
        $this->calculateMeasures();
        $this->output->storePos();
        $this->started = true;
    }

    /**
     * Draw the progress bar.
     * Prints the progress-bar to the screen. If start() has not been called 
     * yet, the current line is used for {@link ezcConsolProgressbar::start()}.
     *
     * @return void
     */
    public function output()
    {
        if ( $this->started === false )
        {
            $this->start();
        }
        $this->output->restorePos();
        $this->generateValues();
        echo $this->insertValues();
    }

    /**
     * Advance the progress bar.
     * Advances the progress bar by $step steps. Redraws the bar by default,
     * using the {@link ezcConsoleProgressbar::output()} method.
     *
     * @param bool  $redraw Whether to redraw the bar immediately.
     * @param float $steps  How far the progress bar should advance on this call.
     * @return void
     */
    public function advance( $redraw = true, $step = 1 ) 
    {
        $this->currentStep += $step;
        if ( $redraw === true && $this->currentStep % $this->options->redrawFrequency === 0 )
        {
            $this->output();
        }
    }

    /**
     * Finish the progress bar.
     * Finishes the bar (jump to 100% if not happened yet,...) and jumps
     * to the next line to allow new output. Also resets the values of the
     * output handler used, if changed.
     *
     * @return void
     */
    public function finish()
    {
        $this->currentStep = $this->numSteps;
        $this->output();
    }

    /**
     * Generate all values to be replaced in the format string. 
     * 
     * @return void
     */
    protected function generateValues()
    {
        // Bar
        $barFilledSpace = ceil( $this->measures['barSpace'] / $this->numSteps * $this->currentStep );
        // Sanitize value if it gets to large by rounding
        $barFilledSpace = $barFilledSpace > $this->measures['barSpace'] ? $this->measures['barSpace'] : $barFilledSpace;
        $bar = str_pad( 
            str_pad( 
                $this->options->progressChar, 
                $barFilledSpace, 
                $this->options->barChar, 
                STR_PAD_LEFT
            ), 
            $this->measures['barSpace'], 
            $this->options->emptyChar, 
            STR_PAD_RIGHT 
        );
        $this->valueMap['bar'] = $bar;

        // Fraction
        $fractionVal = sprintf( 
            $this->options->fractionFormat,
            ( $fractionVal = ( $this->options->step * $this->currentStep ) / $this->max * 100 ) > 100 ? 100 : $fractionVal
        );
        $this->valueMap['fraction'] = str_pad( 
            $fractionVal, 
            strlen( sprintf( $this->options->fractionFormat, 100 ) ),
            ' ',
            STR_PAD_LEFT
        );

        // Act / max
        $actVal = sprintf(
            $this->options->actFormat,
            ( $actVal = $this->currentStep * $this->options->step ) > $this->max ? $this->max : $actVal
        );
        $this->valueMap['act'] = str_pad( 
            $actVal, 
            strlen( sprintf( $this->options->actFormat, $this->max ) ),
            ' ',
            STR_PAD_LEFT
        );
        $this->valueMap['max'] = sprintf( $this->options->maxFormat, $this->max );
    }

    /**
     * Insert values into bar format string. 
     * 
     * @return void
     */
    protected function insertValues()
    {
        $bar = $this->options->formatString;
        foreach ( $this->valueMap as $name => $val )
        {
            $bar = str_replace( "%{$name}%", $val, $bar );
        }
        return $bar;
    }

    /**
     * Calculate several measures necessary to generate a bar. 
     * 
     * @return void
     */
    protected function calculateMeasures()
    {
        // Calc number of steps bar goes through
        $this->numSteps = ( int ) round( $this->max / $this->options->step );
        // Calculate measures
        $this->measures['fixedCharSpace'] = strlen( $this->stripEscapeSequences( $this->insertValues() ) );
        if ( strpos( $this->options->formatString,'%max%' ) !== false )
        {
            $this->measures['maxSpace'] = strlen( sprintf( $this->options->maxFormat, $this->max ) );

        }
        if ( strpos( $this->options->formatString, '%act%' ) !== false )
        {
            $this->measures['actSpace'] = strlen( sprintf( $this->options->actFormat, $this->max ) );
        }
        if ( strpos( $this->options->formatString, '%fraction%' ) !== false )
        {
            $this->measures['fractionSpace'] = strlen( sprintf( $this->options->fractionFormat, 100 ) );
        }
        $this->measures['barSpace'] = $this->options->width - array_sum( $this->measures );
    }

    /**
     * Strip all escape sequences from a string to measure it's size correctly. 
     * 
     * @param mixed $str 
     * @return void
     */
    protected function stripEscapeSequences( $str )
    {
        return preg_replace( '/\033\[[0-9a-f;]*m/i', '', $str  );
    }
}
?>
