<?php
/**
 * File containing the ezcConsoleProgressbarOptions class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Struct class to store the options of the ezcConsoleOutput class.
 * This class stores the options for the {@link ezcConsoleOutput} class.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleProgressbarOptions extends ezcBaseOptions
{
    /**
     * The character to fill the bar with, during progress indication. 
     * 
     * @var string
     */

    protected $barChar = "+";
    /**
     * The character to pre-fill the bar, before indicating progress. 
     * 
     * @var string
     */
    protected $emptyChar = "-";

    /**
     * The format string to describe the complete progressbar. 
     * 
     * @var string
     */
    protected $formatString = "%act% / %max% [%bar%] %fraction%%";

    /**
     * Format to display the fraction value. 
     * 
     * @var string
     */
    protected $fractionFormat = "%01.2f";

    /**
     * The character for the end of the progress area (the arrow!).
     * 
     * @var string
     */
    protected $progressChar = ">";

    /**
     * How often to redraw the progressbar (on every Xth call to advance()).
     * 
     * @var int
     */
    protected $redrawFrequency = 1;

    /**
     * How many steps to advance the progressbar on each call to advance().
     * 
     * @var int
     */
    protected $step = 1;

    /**
     * The width of the bar itself. 
     * 
     * @var int
     */
    protected $width = 78;

    /**
     * The format to display the actual value with. 
     * 
     * @var string
     */
    protected $actFormat = '%.0f';

    /**
     * The format to display the actual value with. 
     * 
     * @var string
     */
    protected $maxFormat = '%.0f';

    /**
     * Option write access.
     * 
     * @throws ezcBasePropertyNotFoundException
     *         If a desired property could not be found.
     * @throws ezcBaseSettingValueException
     *         If a desired property value is out of range.
     *
     * @param string $key Name of the property.
     * @param mixed $value  The value for the property.
     * @return void
     */
    public function __set( $key, $value )
    {
        switch ( $key )
        {
            case "barChar":
            case "emptyChar":
            case "progressChar":
            case "formatString":
            case "fractionFormat":
            case "actFormat":
            case "maxFormat":
                if ( strlen( $value ) < 1 )
                {
                    throw new ezcBaseSettingValueException( $key, $value, 'string, not empty' );
                }
                break;
            case "width":
                if ( !is_int( $value ) || $value < 5 )
                {
                    throw new ezcBaseSettingValueException( $key, $value, 'int >= 5' );
                }
                break;
            case "redrawFrequency":
            case "step":
                if ( ( !is_int( $value ) && !is_float( $value ) ) || $value < 1 )
                {
                    throw new ezcBaseSettingValueException( $key, $value, 'int > 0' );
                }
                break;
            default:
                throw new ezcBaseSettingNotFoundException( $key );
        }
        $this->$key = $value;
    }
}

?>
