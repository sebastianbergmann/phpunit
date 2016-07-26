<?php

/*
 * http://www.pensandoenred.com
 * Copyright (C) 2011 Mario Nunes
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 *
 * Repository of this class: https://github.com/mariotux/php-simple-stub
 *
 */

class SimpleStub {

    private $_object = null;
    private $_call = array('name' => null, 'args' => null);

    public function __construct($object = null) {
        $this->_object = $object;
    }

    public function __call($name, $arguments) {
        $this->_call['name'] = $name;
        $this->_call['args'] = $arguments;
        return $this;
    }

    public function returnValue($argument) {
        if (method_exists($this->_object, $this->_call['name'])) {
            return call_user_func_array(array($this->_object, $this->_call['name']), $this->_call['args']);
        }
        return $argument;
    }

}

?>
