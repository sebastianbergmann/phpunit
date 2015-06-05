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
 * @package    PHPUnit
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.0
 */
class Framework_Constraint_JsonMatchesTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider evaluateDataprovider
     * @covers PHPUnit_Framework_Constraint_JsonMatches::evaluate
     * @covers PHPUnit_Framework_Constraint_JsonMatches::matches
     * @covers PHPUnit_Framework_Constraint_JsonMatches::__construct
     */
    public function testEvaluate($expected, $jsonOther, $jsonValue)
    {
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches($jsonValue);
        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_JsonMatches::toString
     */
    public function testToString()
    {
        $jsonValue = json_encode(array('Mascott' => 'Tux'));
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches($jsonValue);

        $this->assertEquals('matches JSON string "' . $jsonValue . '"', $constraint->toString());
    }


    public static function evaluateDataprovider()
    {
        return array(
            'valid JSON' => array(true, json_encode(array('Mascott' => 'Tux')), json_encode(array('Mascott' => 'Tux'))),
            'error syntax' => array(false, '{"Mascott"::}', json_encode(array('Mascott' => 'Tux'))),
            'error UTF-8' => array(false, json_encode('\xB1\x31'), json_encode(array('Mascott' => 'Tux'))),
            'invalid JSON in class instantiation' => array(false, json_encode(array('Mascott' => 'Tux')), '{"Mascott"::}'),
            'string type not equals number' => array(false, '{"age": "5"}', '{"age": 5}'),
            'string type not equals boolean' => array(false, '{"age": "true"}', '{"age": true}'),
            'string type not equals null' => array(false, '{"age": "null"}', '{"age": null}'),
            'object fields are unordered' => array(true, '{"first":1, "second":"2"}', '{"second":"2", "first":1}'),
            'child object fields are unordered' => array(true, '{"Mascott": {"name":"Tux", "age":5}}', '{"Mascott": {"age":5, "name":"Tux"}}'),
			'null field different from missing field' => array(false, '{"present": true, "missing": null}', '{"present": true}'),
            'array elements are ordered' => array(false, '["first", "second"]', '["second", "first"]'),
            'single boolean valid json' => array(true, 'true', 'true'),
            'single number valid json' => array(true, '5.3', '5.3'),
            'single null valid json' => array(true, 'null', 'null'),
        );
    }
}
