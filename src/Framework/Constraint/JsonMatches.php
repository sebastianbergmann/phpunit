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
 * Asserts whether or not two JSON objects are equal.
 *
 * @package    PHPUnit
 * @subpackage Framework_Constraint
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.7.0
 */
class PHPUnit_Framework_Constraint_JsonMatches extends PHPUnit_Framework_Constraint
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Creates a new constraint.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * This method can be overridden to implement the evaluation algorithm.
     *
     * @param  mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($other)
    {
        list($error, $recodedOther) = $this->canonicalizeJson($other);
        if ($error) {
            return false;
        }

        list ($error, $recodedValue) = $this->canonicalizeJson($this->value);
        if ($error) {
            return false;
        }

        return $recodedOther == $recodedValue;
    }

    /*
     * To allow comparison of JSON strings, first process them into a consistent
     * format so that they can be compared as strings.
     * @return array ($error, $canonicalized_json)  The $error parameter is used
     * to indicate an error decoding the json.  This is used to avoid ambiguity
     * with JSON strings consisting entirely of 'null' or 'false'.
     */
    private function canonicalizeJson($json)
    {
        $decodedJson = json_decode($json, true);
        if (json_last_error()) {
            return array(true, null);
        }
        $this->recursiveSort($decodedJson);
        $reencodedJson = json_encode($decodedJson);
        return array(false, $reencodedJson);
    }

    /*
     * JSON object keys are unordered while PHP array keys are ordered.
     * Sort all array keys to ensure both the expected and actual values have
     * their keys in the same order.
     */
    private function recursiveSort(&$json)
    {
        if (is_array($json)) {
            ksort($json);
            foreach ($json as $key => &$value) {
                $this->recursiveSort($value);
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return sprintf(
            'matches JSON string "%s"',
            $this->value
        );
    }
}
