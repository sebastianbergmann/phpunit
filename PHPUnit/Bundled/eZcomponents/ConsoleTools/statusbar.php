<?php
/**
 * File containing the ezcConsoleStatusbar class.
 *
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Creating  and maintaining status-bars to be printed to the console. 
 *
 * <code>
 * // Construction
 * $status = new ezcConsoleStatusbar( new ezcConsoleOutput() );
 *
 * // Set option
 * $status->options['successChar'] = '*';
 *
 * // Run statusbar
 * foreach ( $files as $file )
 * {
 *      $res = $file->upload();
 *      // Add status if form of bool true/false to statusbar.
 *      $status->add( $res ); // $res is true or false
 * }
 *
 * // Retreive and display final statusbar results
 * $msg = $status->getSuccess() . ' succeeded, ' . $status->getFailure() . ' failed.';
 * $out->outputText( "Finished uploading files: $msg\n" );
 * </code>
 *  
 * 
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleStatusbar
{
    /**
     * Options
     *
     * @var ezcConsoleStatusbarOptions
     */
    protected $options;

    /**
     * The ezcConsoleOutput object to use.
     *
     * @var ezcConsoleOutput
     */
    protected $outputHandler;

    /**
     * Counter for success and failure outputs. 
     * 
     * @var array(bool=>int)
     */
    protected $counter = array( 
        true  => 0,
        false => 0,
    );

    /**
     * Creates a new status bar.
     *
     * @param ezcConsoleOutput $outHandler Handler to utilize for output
     * @param array(string=>string) $options       Options
     *
     * @see ezcConsoleStatusbar::$options
     */
    public function __construct( ezcConsoleOutput $outHandler, array $options = array() )
    {
        $this->outputHandler = $outHandler;
        $this->options = new ezcConsoleStatusbarOptions( $options );
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
                break;
        }
        if ( isset( $this->options->$key ) )
        {
            return $this->options->$key;
        }
        throw new ezcBasePropertyNotFoundException( $key );
    }

    /**
     * Set new options.
     * This method allows you to change the options of a statusbar.
     *  
     * @param array(string=>string)|ezcConsoleOutputOptions $options The options to set.
     *
     * @throws ezcBaseSettingNotFoundException
     *         If you tried to set a non-existent option value.
     * @throws ezcBaseSettingValueException
     *         If the value is not valid for the desired option.
     * @throws ezcBaseValueException
     *         If you submit neither an array nor an instance of 
     *         ezcConsoleOutputOptions.
     */
    public function setOptions( $options ) 
    {
        if ( is_array( $options ) ) 
        {
            $this->options->merge( $options );
        } 
        else if ( $options instanceof ezcConsoleStatusbarOptions ) 
        {
            $this->options = $options;
        }
        else
        {
            throw new ezcBaseValueException( "options", $options, "instance of ezcConsoleStatusbarOptions" );
        }
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
            case 'successChar':
            case 'failureChar':
                if ( strlen( $val ) < 1 )
                {
                    throw new ezcBaseValueException( $key, $val, 'string, not empty' );
                }
                break;
            default:
                throw new ezcBasePropertyNotFoundException( $key );
        }
        $this->options[$key] = $val;
    }
 
    /**
     * Property isset access.
     * 
     * @param string $key Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $key )
    {
        return isset( $this->options[$key] );
    }
    
    /**
     * Returns the current options.
     * Returns the options currently set for this progressbar.
     * 
     * @return ezcConsoleStatusbarOptions The current options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add a status to the status bar.
     * Adds a new status to the bar which is printed immediately. If the
     * cursor is currently not at the beginning of a line, it will move to
     * the next line.
     *
     * @param bool $status Print successChar on true, failureChar on false.
     * @return void
     */
    public function add( $status )
    {
        switch ( $status )
        {
            case true:
                $this->outputHandler->outputText( $this->options['successChar'], 'success' );
                break;

            case false:
                $this->outputHandler->outputText( $this->options['failureChar'], 'failure' );
                break;
            
            default:
                trigger_error( 'Unknown status '.var_export( $status, true ).'.', E_USER_WARNING );
                return;
        }
        $this->counter[$status]++;
    }

    /**
     * Reset the state of the status-bar object to its initial one. 
     * 
     * @return void
     */
    public function reset()
    {
        foreach ( $this->counter as $status => $count )
        {
            $this->counter[$status] = 0;
        }
    }

    /**
     * Returns number of successes during the run.
     * Returns the number of success characters printed from this status bar.
     * 
     * @return int Number of successes.
     */
    public function getSuccessCount()
    {
        return $this->counter[true];
    }

    /**
     * Returns number of failures during the run.
     * Returns the number of failure characters printed from this status bar.
     * 
     * @return int Number of failures.
     */
    public function getFailureCount()
    {
        return $this->counter[false];
    }
}
?>
