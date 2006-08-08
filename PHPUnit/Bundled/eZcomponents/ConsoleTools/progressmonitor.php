<?php
/**
 * File containing the ezcConsoleProgressMonitor class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Printing structured status information on the console. 
 *
 * <code>
 * // Construction
 * $status = new ezcConsoleProgressMonitor( new ezcConsoleOutput(), 42 );
 *
 * // Run statusbar
 * foreach ( $files as $file )
 * {
 *      $res = $file->upload();
 *      // Add a status-entry to be printed.
 *      $status->addEntry( 'UPLOAD', $file->path );
 *      // Result like "    2.5% UPLOAD /var/upload/test.png"
 * }
 * </code>
 *  
 * 
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleProgressMonitor
{
    /**
     * Options
     *
     * @var ezcConsoleProgressMonitorOptions
     */
    protected $options;

    /**
     * The ezcConsoleOutput object to use.
     *
     * @var ezcConsoleOutput
     */
    protected $outputHandler;

    /**
     * Counter for the items already printed. 
     * 
     * @var int
     */
    protected $counter = 0;

    /**
     * The number of entries to expect. 
     * 
     * @var int
     */
    protected $max;

    /**
     * Creates a new progress monitor.
     *
     * @param ezcConsoleOutput $outHandler Handler to utilize for output
     * @param int $max                     Number of items to expect
     * @param array(string=>string)        Options.
     *
     * @see ezcConsoleProgressMonitor::$options
     */
    public function __construct( ezcConsoleOutput $outHandler, $max, array $options = array() )
    {
        $this->outputHandler = $outHandler;
        $this->max = $max;
        $this->options = new ezcConsoleProgressMonitorOptions( $options );
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
        throw new ezcBasePropertyNotFoundException( $key );
    }
    
    /**
     * Property write access.
     * 
     * @param string $propertyName Name of the property.
     * @param mixed $val  The value for the property.
     *
     * @throws ezcBaseValueException 
     *         If a the value for the property options is not an instance of 
     *         ezcConsoleProgressMonitorOptions. 
     * @return void
     */
    public function __set( $propertyName, $val )
    {
        switch ( $propertyName ) 
        {
            case 'options':
                if ( !( $val instanceof ezcConsoleProgressMonitorOptions ) )
                {
                    throw new ezcBaseValueException( $key, $val, 'instance of ezcConsoleProgressMonitorOptions' );
                }
                $this->options = $val;
                return;
        }
        throw new ezcBasePropertyNotFoundException( $propertyName );
    }
    
    
    /**
     * Property isset access.
     * 
     * @param string $propertyName Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'options':
                return true;
        }
        return false;
    }

    /**
     * Set new options.
     * This method allows you to change the options of an progress monitor.
     *  
     * @param ezcConsoleProgressMonitorOptions $options The options to set.
     *
     * @throws ezcBaseSettingNotFoundException
     *         If you tried to set a non-existent option value. 
     * @throws ezcBaseSettingValueException
     *         If the value is not valid for the desired option.
     * @throws ezcBaseValueException
     *         If you submit neither an array nor an instance of 
     *         ezcConsoleProgressMonitorOptions.
     */
    public function setOptions( $options ) 
    {
        if ( is_array( $options ) ) 
        {
            $this->options->merge( $options );
        } 
        else if ( $options instanceof ezcConsoleProgressMonitorOptions ) 
        {
            $this->options = $options;
        }
        else
        {
            throw new ezcBaseValueException( "options", $options, "instance of ezcConsoleProgressMonitorOptions" );
        }
    }

    /**
     * Returns the currently set options.
     * Returns the currently set option array.
     * 
     * @return ezcConsoleProgressMonitorOptions The options.
     */
    public function getOptions()
    {
        return $this->options;
    }
 
    /**
     * Print a status entry.
     * Prints a new status entry to the console and updates the internal counter.
     *
     * @param string $tag  The tag to print (second argument in the 
     *                     formatString).
     * @param string $data The data to be printed in the status entry (third 
     *                     argument in the format string).
     * @return void
     */
    public function addEntry( $tag, $data )
    {
        $this->counter++;
        $percentage = $this->counter / $this->max * 100;

        $this->outputHandler->outputLine(
            sprintf(
                $this->options['formatString'],
                $percentage,
                $tag,
                $data
            )
        );
    }
}
?>
