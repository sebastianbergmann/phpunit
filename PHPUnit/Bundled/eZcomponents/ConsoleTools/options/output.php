<?php
/**
 * File containing the ezcConsoleOutputOptions class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Struct class to store the options of the ezcConsoleOutput class.
 *
 * This class stores the options for the {@link ezcConsoleOutput} class.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOutputOptions extends ezcBaseOptions
{
    /**
     * Determines the level of verbosity. 
     * 
     * @var int
     */
    protected $verbosityLevel = 1;

    /**
     * Determines, whether text is automatically wrapped after a specific amount
     * of characters in a line. If set to 0 (default), lines will not be wrapped
     * automatically.
     * 
     * @var int
     */
    protected $autobreak = 0;

    /**
     * Wether to use formatting or not. 
     * 
     * @var bool
     */
    protected $useFormats = true;

    /**
     * Construct a new options object.
     *
     * NOTE: For BC reasons the old method of instanciating this class is kept,
     * but the usage of the new versoion is highly incouraged.
     * 
     * @param array(string=>mixed) $options The initial options to set.
     * @return void
     *
     * @throws ezcBasePropertyNotFoundException
     *         If a the value for the property options is not an instance of
     * @throws ezcBaseValueException
     *         If a the value for a property is out of range.
     */
    public function __construct()
    {
        $args = func_get_args();
        if ( func_num_args() === 1 && is_array( $args[0] ) )
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
                        $this->__set( "verbosityLevel", $val );
                        break;
                    case 1:
                        $this->__set( "autobreak", $val );
                        break;
                    case 2:
                        $this->__set( "useFormats", $val );
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
            case 'verbosityLevel':
            case 'autobreak':
                if ( !is_int( $val ) || $val < 0 )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'int >= 0' );
                }
                break;
            case 'useFormats':
                if ( !is_bool( $val ) )
                {
                    throw new ezcBaseSettingValueException( $propertyName, $val, 'bool' );
                }
                break;
            default:
                throw new ezcBaseSettingNotFoundException( $propertyName );
        }
        $this->$propertyName = $val;
    }
}

?>
