<?php
/**
 * File containing the ezcConsoleOptionRule class.
 *
 * @package ConsoleTools
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Struct class to store a parameter rule.
 *
 * This struct stores relation rules between parameters. A relation consists of
 * a parameter that the relation refers to and optionally the value(s) the 
 * referred parameter may have assigned. Rules may be used for dependencies and 
 * exclusions between parameters.
 *
 * The ezcConsoleOptionRule class has the following properties:
 * - <b>option</b> <i>ezcConsoleOption</i>, contains the parameter that this rule refers to.
 * - <b>values</b> <i>array(string)</i>, contains a list of values that are accepted.
 *
 * @see ezcConsoleOption
 * 
 * @package ConsoleTools
 * @version 1.1
 */
class ezcConsoleOptionRule
{
    /**
     * Property array containing this class' properties.
     *
     * @var array
     */
    protected $properties = array( 
        'option' => null,
        'values' => array(),
    );

    /**
     * Creates a new parameter rule.
     *
     * Creates a new parameter rule. Per default the $values parameter
     * is an empty array, which determines that the parameter may accept any
     * value. To indicate that a parameter may only have certain values,
     * place them inside tha $values array. For example to indicate a parameter
     * may have the values 'a', 'b' and 'c' use:
     * <code>
     * $rule = new ezcConsoleOptionRule( $option, array( 'a', 'b', 'c' ) );
     * </code>
     * If you want to allow only 1 specific value for a parameter, you do not
     * need to wrap this into an array, when creating the rule. Simply use
     * <code>
     * $rule = new ezcConsoleOptionRule( $option, 'a' );
     * </code>
     * to create a rule, that allows the desired parameter only to accept the
     * value 'a'.
     *
     * @param ezcConsoleOption $option The parameter to refer to.
     * @param mixed $values The values $option may have assigned.
     */
    public function __construct( ezcConsoleOption $option, array $values = array() )
    {
        $this->__set( 'option', $option );
        $this->__set( 'values', $values );
    }
    
    /**
     * Property read access overloading.
     * Gain read access to properties.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the property tried to access does not exist.
     * 
     * @param string $propertyName Name of the property to access.
     * @return mixed Value of the property.
     */
    public function __get( $propertyName ) 
    {
        switch ( $propertyName )
        {
            case 'option':
                return $this->properties['option'];
            case 'values':
                return $this->properties['values'];
        }
        throw new ezcBasePropertyNotFoundException( $propertyName );
    }
    
    /**
     * Property read access overloading.
     * Gain read access to properties.
     *
     * @throws ezcBasePropertyNotFoundException
     *         If the property tried to access does not exist.
     * @throws ezcBaseValueException
     *         If the value for a property is not in the correct range.
     * 
     * @param string $propertyName Name of the property to access.
     * @return void
     */
    public function __set( $propertyName, $val ) 
    {
        switch ( $propertyName )
        {
            case 'option':
                if ( !( $val instanceof ezcConsoleOption ) )
                {
                    throw new ezcBaseValueException( $propertyName, $val, 'ezcConsoleOption' );
                }
                $this->properties['option'] = $val;
                return;
            case 'values':
                if ( !is_array( $val ) )
                {
                    throw new ezcBaseValueException( $propertyName, $val, 'array' );
                }
                $this->properties['values'] = $val;
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
            case 'option':
            case 'values':
                return true;
        }
        return false;
    }

}

?>
