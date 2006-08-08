<?php
/**
 * File containing the ezcConsoleTableOptions class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Struct class to store the options of the ezcConsoleTable class.
 * This class stores the options for the {@link ezcConsoleTable} class.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleTableOptions extends ezcBaseOptions
{
    /**
     * Column width: Either an array of column widths like:
     * <code>
     * array( 
     *      0 => 10,
     *      1 => 30,
     *      2 => 10,
     * )
     * </code>
     * To have the first column 10 characters wide, the second 30 and the 3rd 10.
     * Alternatively the string "auto" to have the columns widths automatically
     * calculated.
     * 
     * @var mixed
     */
    protected $colWidth = "auto";

    /**
     * Wrap style of text contained in strings.
     * @see ezcConsoleTable::WRAP_AUTO
     * @see ezcConsoleTable::WRAP_NONE
     * @see ezcConsoleTable::WRAP_CUT
     * 
     * @var int
     */
    protected $colWrap = ezcConsoleTable::WRAP_AUTO;

    /**
     * Standard column alignment, applied to cells that have to explicit
     * alignment assigned.
     *
     * @see ezcConsoleTable::ALIGN_LEFT
     * @see ezcConsoleTable::ALIGN_RIGHT
     * @see ezcConsoleTable::ALIGN_CENTER
     * @see ezcConsoleTable::ALIGN_DEFAULT
     * 
     * @var int
     */
    protected $defaultAlign = ezcConsoleTable::ALIGN_LEFT;

    /**
     * Padding characters for side padding between data and lines. 
     * 
     * @var string
     */
    protected $colPadding = " ";

    /**
     * Type of the given table width (fixed or maximal value).
     * 
     * @var int
     */
    protected $widthType = ezcConsoleTable::WIDTH_MAX;
        
    /**
     * Character to use for drawing vertical lines. 
     * 
     * @var string
     */
    protected $lineVertical = "-";

    /**
     * Character to use for drawing horizontal lines. 
     * 
     * @var string
     */
    protected $lineHorizontal = "|";

    /**
     * Character to use for drawing line corners.
     * 
     * @var string
     */
    protected $corner = "+";
    
    /**
     * Standard column content format, applied to cells that have "default" as
     * the content format.
     * 
     * @var string
     */
    protected $defaultFormat = "default";

    /**
     * Standard border format, applied to rows that have 'default' as the
     * border format.
     * 
     * @var string
     */
    protected $defaultBorderFormat = "default";
    
    /**
     * Construct a new options object.
     *
     * NOTE: For backwards compatibility reasons the old method of instantiating this class is kept,
     * but the usage of the new version (providing an option array) is highly encouraged.
     * 
     * @param array(string=>mixed) $options The initial options to set.
     * @return void
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the value for the property options is not an instance of
     * @throws ezcBaseValueException
     *         If the value for a property is out of range.
     */
    public function __construct()
    {
        $args = func_get_args();
        if ( func_num_args() === 1 && is_array( $args[0] ) && !is_int( key( $args[0] ) ) )
        {
            parent::__construct( $args[0] );
        }
        else
        {
            foreach ( $args as $id => $val )
            {
                switch ( $id )
                {
                    case 0:
                        $this->__set( 'colWidth', $val );
                        break;
                    case 1:
                        $this->__set( 'colWrap', $val );
                        break;
                    case 2:
                        $this->__set( 'defaultAlign', $val );
                        break;
                    case 3:
                        $this->__set( 'colPadding', $val );
                        break;
                    case 4:
                        $this->__set( 'widthType', $val );
                        break;
                    case 5:
                        $this->__set( 'lineVertical', $val );
                        break;
                    case 6:
                        $this->__set( 'lineHorizontal', $val );
                        break;
                    case 7:
                        $this->__set( 'corner', $val );
                        break;
                    case 8:
                        $this->__set( 'defaultFormat', $val );
                        break;
                    case 9:
                        $this->__set( 'defaultBorderFormat', $val );
                        break;
                }
            }
        }
    }

    /**
     * Property write access.
     * 
     * @throws ezcBasePropertyNotFoundException
     *         If a desired property could not be found.
     * @throws ezcBaseSettingValueException
     *         If a desired property value is out of range.
     *
     * @param string $propertyName Name of the property.
     * @param mixed $val  The value for the property.
     * @return void
     */
    public function __set( $propertyName, $val )
    {
        switch ( $propertyName )
        {
            case 'colWidth':
                if ( !is_array( $val ) && is_string( $val ) && $val !== 'auto' )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'array(int) or "auto"' );
                }
                break;
            case 'colWrap':
                if ( $val !== ezcConsoleTable::WRAP_AUTO && $val !== ezcConsoleTable::WRAP_NONE && $val !== ezcConsoleTable::WRAP_CUT )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'ezcConsoleTable::WRAP_AUTO, ezcConsoleTable::WRAP_NONE, ezcConsoleTable::WRAP_CUT' );
                }
                break;
            case 'defaultAlign':
                if ( $val !== ezcConsoleTable::ALIGN_DEFAULT && $val !== ezcConsoleTable::ALIGN_LEFT && $val !== ezcConsoleTable::ALIGN_CENTER && $val !== ezcConsoleTable::ALIGN_RIGHT )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'ezcConsoleTable::ALIGN_DEFAULT, ezcConsoleTable::ALIGN_LEFT, ezcConsoleTable::ALIGN_CENTER, ezcConsoleTable::ALIGN_RIGHT' );
                }
                break;
            case 'colPadding':
                if ( !is_string( $val ) )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'string' );
                }
                break;
            case 'widthType':
                if ( $val !== ezcConsoleTable::WIDTH_MAX && $val !== ezcConsoleTable::WIDTH_FIXED )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'ezcConsoleTable::WIDTH_MAX, ezcConsoleTable::WIDTH_FIXED' );
                }
                break;
            case 'lineVertical':
            case 'lineHorizontal':
            case 'corner':
                if ( !is_string( $val ) && strlen( $val ) !== 1 )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'string, length = 1' );
                }
                break;
            case 'defaultFormat':
                if ( !is_string( $val ) || strlen( $val ) < 1 )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'string, length = 1' );
                }
                break;
            case 'defaultBorderFormat':
                if ( !is_string( $val ) || strlen( $val ) < 1 )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'string, length = 1' );
                }
                break;
            default:
                throw new ezcBaseSettingNotFoundException( $propertyName );
        }
        $this->$propertyName = $val;
    }
}

?>
