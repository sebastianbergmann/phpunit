<?php
/**
 * File containing the ezcConsoleOutputFormats class.
 *
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Class to store the collection for formating classes.
 *
 * This class stores objects of {@link ezcConsoleOutputFormat}, which
 * represents a format option set for {@link ezcConsoleOutput}.
 *
 * <code>
 * // New ezcConsoleOutput 
 * // $output->format is instance of ezcConsoleOutputFormats.
 * $output = new ezcConsoleOutput();
 * 
 * // Default format - color = blue
 * $output->formats->default->color = 'blue';
 * // Default format - weight = bold
 * $output->formats->default->style = array( 'bold' );
 *
 * // New format "important" - color = red
 * $output->formats->important->color = 'red';
 * // Format "important" - background color = black
 * $output->formats->important->bgcolor = 'black';
 * </code>
 * 
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleOutputFormats
{
    /**
     * Array of ezcConsoleOutputFormat.
     * 
     * @var array(ezcConsoleOutputFormat)
     */
    protected $formats = array();

    /**
     * Create a new ezcConsoleOutputFormats object.
     *
     * Creates a new, empty object of this class. It also adds a default
     * format.
     */
    public function __construct()
    {
        $this->formats['default'] = new ezcConsoleOutputFormat();
        $this->formats['success'] = new ezcConsoleOutputFormat();
        $this->formats['success']->color = 'green';
        $this->formats['success']->style = array( 'bold' );
        $this->formats['failure'] = new ezcConsoleOutputFormat();
        $this->formats['failure']->color = 'red';
        $this->formats['failure']->style = array( 'bold' );
    }
    
    /**
     * Read access to the formats.
     *
     * Formats are accessed directly like properties of this object. If a
     * format does not exist, it is created on the fly (using default values),
     * 
     * @param string $formatName
     * @return ezcConsoleOutputFormat The format.
     */
    public function __get( $formatName )
    {
        if ( !isset( $this->formats[$formatName] ) )
        {
            $this->formats[$formatName] = new ezcConsoleOutputFormat();
        }
        return $this->formats[$formatName];
    }

    /**
     * Write access to the formats.
     *
     * Formats are accessed directly like properties of this object. If a
     * format does not exist, it is created on the fly (using default values),
     * 
     * @param string $formatName
     * @param ezcConsoleOutputFormat $val The format defintion.
     * @return void
     */
    public function __set( $formatName, ezcConsoleOutputFormat $val )
    {
        $this->formats[$formatName] = $val;
    }
 
    /**
     * Property isset access.
     * 
     * @param string $formatName Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $formatName )
    {
        return isset( $this->formats[$formatName] );
    }

}

?>
