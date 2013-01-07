<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

/**
 * XML helpers.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_XML
{
    /**
     * @param  string $string
     * @return string
     * @author Kore Nordmann <mail@kore-nordmann.de>
     * @since  Method available since Release 3.4.6
     */
    public static function prepareString($string)
    {
        return preg_replace_callback(
          '([\\x00-\\x04\\x0b\\x0c\\x0e-\\x1f\\x7f])',
          function ($matches)
          {
              return sprintf('&#x%02x;', ord($matches[1]));
          },
          htmlspecialchars(
            PHPUnit_Util_String::convertToUtf8($string), ENT_COMPAT, 'UTF-8'
          )
        );
    }

    /**
     * Loads an XML (or HTML) file into a DOMDocument object.
     *
     * @param  string  $filename
     * @param  boolean $isHtml
     * @param  boolean $xinclude
     * @return DOMDocument
     * @since  Method available since Release 3.3.0
     */
    public static function loadFile($filename, $isHtml = FALSE, $xinclude = FALSE)
    {
        $reporting = error_reporting(0);
        $contents  = file_get_contents($filename);
        error_reporting($reporting);

        if ($contents === FALSE) {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Could not read "%s".',
                $filename
              )
            );
        }

        return self::load($contents, $isHtml, $filename, $xinclude);
    }

    /**
     * Load an $actual document into a DOMDocument.  This is called
     * from the selector assertions.
     *
     * If $actual is already a DOMDocument, it is returned with
     * no changes.  Otherwise, $actual is loaded into a new DOMDocument
     * as either HTML or XML, depending on the value of $isHtml. If $isHtml is
     * false and $xinclude is true, xinclude is performed on the loaded
     * DOMDocument.
     *
     * Note: prior to PHPUnit 3.3.0, this method loaded a file and
     * not a string as it currently does.  To load a file into a
     * DOMDocument, use loadFile() instead.
     *
     * @param  string|DOMDocument  $actual
     * @param  boolean             $isHtml
     * @param  string              $filename
     * @param  boolean             $xinclude
     * @return DOMDocument
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     * @author Tobias Schlitt <toby@php.net>
     */
    public static function load($actual, $isHtml = FALSE, $filename = '', $xinclude = FALSE)
    {
        if ($actual instanceof DOMDocument) {
            return $actual;
        }

        $document  = new DOMDocument;

        $internal  = libxml_use_internal_errors(TRUE);
        $message   = '';
        $reporting = error_reporting(0);

        if ($isHtml) {
            $loaded = $document->loadHTML($actual);
        } else {
            $loaded = $document->loadXML($actual);
        }

        if ('' !== $filename) {
            // Necessary for xinclude
            $document->documentURI = $filename;
        }

        if (!$isHtml && $xinclude) {
            $document->xinclude();
        }

        foreach (libxml_get_errors() as $error) {
            $message .= $error->message;
        }

        libxml_use_internal_errors($internal);
        error_reporting($reporting);

        if ($loaded === FALSE) {
            if ($filename != '') {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    'Could not load "%s".%s',

                    $filename,
                    $message != '' ? "\n" . $message : ''
                  )
                );
            } else {
                throw new PHPUnit_Framework_Exception($message);
            }
        }

        return $document;
    }

    /**
     *
     *
     * @param  DOMNode $node
     * @return string
     * @since  Method available since Release 3.4.0
     */
    public static function nodeToText(DOMNode $node)
    {
        if ($node->childNodes->length == 1) {
            return $node->nodeValue;
        }

        $result = '';

        foreach ($node->childNodes as $childNode) {
            $result .= $node->ownerDocument->saveXML($childNode);
        }

        return $result;
    }

    /**
     *
     *
     * @param  DOMNode $node
     * @since  Method available since Release 3.3.0
     * @author Mattis Stordalen Flister <mattis@xait.no>
     */
    public static function removeCharacterDataNodes(DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
                if (($child = $node->childNodes->item($i)) instanceof DOMCharacterData) {
                    $node->removeChild($child);
                }
            }
        }
    }

    /**
     * "Convert" a DOMElement object into a PHP variable.
     *
     * @param  DOMElement $element
     * @return mixed
     * @since  Method available since Release 3.4.0
     */
    public static function xmlToVariable(DOMElement $element)
    {
        $variable = NULL;

        switch ($element->tagName) {
            case 'array': {
                $variable = array();

                foreach ($element->getElementsByTagName('element') as $element) {
                    $value = self::xmlToVariable($element->childNodes->item(1));

                    if ($element->hasAttribute('key')) {
                        $variable[(string)$element->getAttribute('key')] = $value;
                    } else {
                        $variable[] = $value;
                    }
                }
            }
            break;

            case 'object': {
                $className = $element->getAttribute('class');

                if ($element->hasChildNodes()) {
                    $arguments       = $element->childNodes->item(1)->childNodes;
                    $constructorArgs = array();

                    foreach ($arguments as $argument) {
                        if ($argument instanceof DOMElement) {
                            $constructorArgs[] = self::xmlToVariable($argument);
                        }
                    }

                    $class    = new ReflectionClass($className);
                    $variable = $class->newInstanceArgs($constructorArgs);
                } else {
                    $variable = new $className;
                }
            }
            break;

            case 'boolean': {
                $variable = $element->nodeValue == 'true' ? TRUE : FALSE;
            }
            break;

            case 'integer':
            case 'double':
            case 'string': {
                $variable = $element->nodeValue;

                settype($variable, $element->tagName);
            }
            break;
        }

        return $variable;
    }

    /**
     * Validate list of keys in the associative array.
     *
     * @param  array $hash
     * @param  array $validKeys
     * @return array
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function assertValidKeys(array $hash, array $validKeys)
    {
        $valids = array();

        // Normalize validation keys so that we can use both indexed and
        // associative arrays.
        foreach ($validKeys as $key => $val) {
            is_int($key) ? $valids[$val] = NULL : $valids[$key] = $val;
        }

        $validKeys = array_keys($valids);

        // Check for invalid keys.
        foreach ($hash as $key => $value) {
            if (!in_array($key, $validKeys)) {
                $unknown[] = $key;
            }
        }

        if (!empty($unknown)) {
            throw new PHPUnit_Framework_Exception(
              'Unknown key(s): ' . implode(', ', $unknown)
            );
        }

        // Add default values for any valid keys that are empty.
        foreach ($valids as $key => $value) {
            if (!isset($hash[$key])) {
                $hash[$key] = $value;
            }
        }

        return $hash;
    }

    /**
     * Parse a CSS selector into an associative array suitable for
     * use with findNodes().
     *
     * @param  string $selector
     * @param  mixed  $content
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function convertSelectToTag($selector, $content = TRUE)
    {
        $selector = trim(preg_replace("/\s+/", " ", $selector));

        // substitute spaces within attribute value
        while (preg_match('/\[[^\]]+"[^"]+\s[^"]+"\]/', $selector)) {
            $selector = preg_replace(
              '/(\[[^\]]+"[^"]+)\s([^"]+"\])/', "$1__SPACE__$2", $selector
            );
        }

        if (strstr($selector, ' ')) {
            $elements = explode(' ', $selector);
        } else {
            $elements = array($selector);
        }

        $previousTag = array();

        foreach (array_reverse($elements) as $element) {
            $element = str_replace('__SPACE__', ' ', $element);

            // child selector
            if ($element == '>') {
                $previousTag = array('child' => $previousTag['descendant']);
                continue;
            }

            $tag = array();

            // match element tag
            preg_match("/^([^\.#\[]*)/", $element, $eltMatches);

            if (!empty($eltMatches[1])) {
                $tag['tag'] = $eltMatches[1];
            }

            // match attributes (\[[^\]]*\]*), ids (#[^\.#\[]*),
            // and classes (\.[^\.#\[]*))
            preg_match_all(
              "/(\[[^\]]*\]*|#[^\.#\[]*|\.[^\.#\[]*)/", $element, $matches
            );

            if (!empty($matches[1])) {
                $classes = array();
                $attrs   = array();

                foreach ($matches[1] as $match) {
                    // id matched
                    if (substr($match, 0, 1) == '#') {
                        $tag['id'] = substr($match, 1);
                    }

                    // class matched
                    else if (substr($match, 0, 1) == '.') {
                        $classes[] = substr($match, 1);
                    }

                    // attribute matched
                    else if (substr($match, 0, 1) == '[' &&
                             substr($match, -1, 1) == ']') {
                        $attribute = substr($match, 1, strlen($match) - 2);
                        $attribute = str_replace('"', '', $attribute);

                        // match single word
                        if (strstr($attribute, '~=')) {
                            list($key, $value) = explode('~=', $attribute);
                            $value             = "regexp:/.*\b$value\b.*/";
                        }

                        // match substring
                        else if (strstr($attribute, '*=')) {
                            list($key, $value) = explode('*=', $attribute);
                            $value             = "regexp:/.*$value.*/";
                        }

                        // exact match
                        else {
                            list($key, $value) = explode('=', $attribute);
                        }

                        $attrs[$key] = $value;
                    }
                }

                if ($classes) {
                    $tag['class'] = join(' ', $classes);
                }

                if ($attrs) {
                    $tag['attributes'] = $attrs;
                }
            }

            // tag content
            if (is_string($content)) {
                $tag['content'] = $content;
            }

            // determine previous child/descendants
            if (!empty($previousTag['descendant'])) {
                $tag['descendant'] = $previousTag['descendant'];
            }

            else if (!empty($previousTag['child'])) {
                $tag['child'] = $previousTag['child'];
            }

            $previousTag = array('descendant' => $tag);
        }

        return $tag;
    }

    /**
     * Parse an $actual document and return an array of DOMNodes
     * matching the CSS $selector.  If an error occurs, it will
     * return FALSE.
     *
     * To only return nodes containing a certain content, give
     * the $content to match as a string.  Otherwise, setting
     * $content to TRUE will return all nodes matching $selector.
     *
     * The $actual document may be a DOMDocument or a string
     * containing XML or HTML, identified by $isHtml.
     *
     * @param  array   $selector
     * @param  string  $content
     * @param  mixed   $actual
     * @param  boolean $isHtml
     * @return false|array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     * @author Tobias Schlitt <toby@php.net>
     */
    public static function cssSelect($selector, $content, $actual, $isHtml = TRUE)
    {
        $matcher = self::convertSelectToTag($selector, $content);
        $dom     = self::load($actual, $isHtml);
        $tags    = self::findNodes($dom, $matcher, $isHtml);

        return $tags;
    }

    /**
     * Parse out the options from the tag using DOM object tree.
     *
     * @param  DOMDocument $dom
     * @param  array       $options
     * @param  boolean     $isHtml
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     * @author Tobias Schlitt <toby@php.net>
     */
    public static function findNodes(DOMDocument $dom, array $options, $isHtml = TRUE)
    {
        $valid = array(
          'id', 'class', 'tag', 'content', 'attributes', 'parent',
          'child', 'ancestor', 'descendant', 'children'
        );

        $filtered = array();
        $options  = self::assertValidKeys($options, $valid);

        // find the element by id
        if ($options['id']) {
            $options['attributes']['id'] = $options['id'];
        }

        if ($options['class']) {
            $options['attributes']['class'] = $options['class'];
        }

        // find the element by a tag type
        if ($options['tag']) {
            if ($isHtml) {
                $elements = self::getElementsByCaseInsensitiveTagName(
                  $dom, $options['tag']
                );
            } else {
                $elements = $dom->getElementsByTagName($options['tag']);
            }

            foreach ($elements as $element) {
                $nodes[] = $element;
            }

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // no tag selected, get them all
        else {
            $tags = array(
              'a', 'abbr', 'acronym', 'address', 'area', 'b', 'base', 'bdo',
              'big', 'blockquote', 'body', 'br', 'button', 'caption', 'cite',
              'code', 'col', 'colgroup', 'dd', 'del', 'div', 'dfn', 'dl',
              'dt', 'em', 'fieldset', 'form', 'frame', 'frameset', 'h1', 'h2',
              'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
              'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link',
              'map', 'meta', 'noframes', 'noscript', 'object', 'ol', 'optgroup',
              'option', 'p', 'param', 'pre', 'q', 'samp', 'script', 'select',
              'small', 'span', 'strong', 'style', 'sub', 'sup', 'table',
              'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
              'tr', 'tt', 'ul', 'var'
            );

            foreach ($tags as $tag) {
                if ($isHtml) {
                    $elements = self::getElementsByCaseInsensitiveTagName(
                      $dom, $tag
                    );
                } else {
                    $elements = $dom->getElementsByTagName($tag);
                }

                foreach ($elements as $element) {
                    $nodes[] = $element;
                }
            }

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by attributes
        if ($options['attributes']) {
            foreach ($nodes as $node) {
                $invalid = FALSE;

                foreach ($options['attributes'] as $name => $value) {
                    // match by regexp if like "regexp:/foo/i"
                    if (preg_match('/^regexp\s*:\s*(.*)/i', $value, $matches)) {
                        if (!preg_match($matches[1], $node->getAttribute($name))) {
                            $invalid = TRUE;
                        }
                    }

                    // class can match only a part
                    else if ($name == 'class') {
                        // split to individual classes
                        $findClasses = explode(
                          ' ', preg_replace("/\s+/", " ", $value)
                        );

                        $allClasses = explode(
                          ' ',
                          preg_replace("/\s+/", " ", $node->getAttribute($name))
                        );

                        // make sure each class given is in the actual node
                        foreach ($findClasses as $findClass) {
                            if (!in_array($findClass, $allClasses)) {
                                $invalid = TRUE;
                            }
                        }
                    }

                    // match by exact string
                    else {
                        if ($node->getAttribute($name) != $value) {
                            $invalid = TRUE;
                        }
                    }
                }

                // if every attribute given matched
                if (!$invalid) {
                    $filtered[] = $node;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by content
        if ($options['content'] !== NULL) {
            foreach ($nodes as $node) {
                $invalid = FALSE;

                // match by regexp if like "regexp:/foo/i"
                if (preg_match('/^regexp\s*:\s*(.*)/i', $options['content'], $matches)) {
                    if (!preg_match($matches[1], self::getNodeText($node))) {
                        $invalid = TRUE;
                    }
                }

                // match empty string
                else if ($options['content'] === '') {
                    if (self::getNodeText($node) !== '') {
                        $invalid = TRUE;
                    }
                }

                // match by exact string
                else if (strstr(self::getNodeText($node), $options['content']) === FALSE) {
                    $invalid = TRUE;
                }

                if (!$invalid) {
                    $filtered[] = $node;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by parent node
        if ($options['parent']) {
            $parentNodes = self::findNodes($dom, $options['parent'], $isHtml);
            $parentNode  = isset($parentNodes[0]) ? $parentNodes[0] : NULL;

            foreach ($nodes as $node) {
                if ($parentNode !== $node->parentNode) {
                    continue;
                }

                $filtered[] = $node;
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by child node
        if ($options['child']) {
            $childNodes = self::findNodes($dom, $options['child'], $isHtml);
            $childNodes = !empty($childNodes) ? $childNodes : array();

            foreach ($nodes as $node) {
                foreach ($node->childNodes as $child) {
                    foreach ($childNodes as $childNode) {
                        if ($childNode === $child) {
                            $filtered[] = $node;
                        }
                    }
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by ancestor
        if ($options['ancestor']) {
            $ancestorNodes = self::findNodes($dom, $options['ancestor'], $isHtml);
            $ancestorNode  = isset($ancestorNodes[0]) ? $ancestorNodes[0] : NULL;

            foreach ($nodes as $node) {
                $parent = $node->parentNode;

                while ($parent && $parent->nodeType != XML_HTML_DOCUMENT_NODE) {
                    if ($parent === $ancestorNode) {
                        $filtered[] = $node;
                    }

                    $parent = $parent->parentNode;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by descendant
        if ($options['descendant']) {
            $descendantNodes = self::findNodes($dom, $options['descendant'], $isHtml);
            $descendantNodes = !empty($descendantNodes) ? $descendantNodes : array();

            foreach ($nodes as $node) {
                foreach (self::getDescendants($node) as $descendant) {
                    foreach ($descendantNodes as $descendantNode) {
                        if ($descendantNode === $descendant) {
                            $filtered[] = $node;
                        }
                    }
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by children
        if ($options['children']) {
            $validChild   = array('count', 'greater_than', 'less_than', 'only');
            $childOptions = self::assertValidKeys(
                              $options['children'], $validChild
                            );

            foreach ($nodes as $node) {
                $childNodes = $node->childNodes;

                foreach ($childNodes as $childNode) {
                    if ($childNode->nodeType !== XML_CDATA_SECTION_NODE &&
                        $childNode->nodeType !== XML_TEXT_NODE) {
                        $children[] = $childNode;
                    }
                }

                // we must have children to pass this filter
                if (!empty($children)) {
                    // exact count of children
                    if ($childOptions['count'] !== NULL) {
                        if (count($children) !== $childOptions['count']) {
                            break;
                        }
                    }

                    // range count of children
                    else if ($childOptions['less_than']    !== NULL &&
                            $childOptions['greater_than'] !== NULL) {
                        if (count($children) >= $childOptions['less_than'] ||
                            count($children) <= $childOptions['greater_than']) {
                            break;
                        }
                    }

                    // less than a given count
                    else if ($childOptions['less_than'] !== NULL) {
                        if (count($children) >= $childOptions['less_than']) {
                            break;
                        }
                    }

                    // more than a given count
                    else if ($childOptions['greater_than'] !== NULL) {
                        if (count($children) <= $childOptions['greater_than']) {
                            break;
                        }
                    }

                    // match each child against a specific tag
                    if ($childOptions['only']) {
                        $onlyNodes = self::findNodes(
                          $dom, $childOptions['only'], $isHtml
                        );

                        // try to match each child to one of the 'only' nodes
                        foreach ($children as $child) {
                            $matched = FALSE;

                            foreach ($onlyNodes as $onlyNode) {
                                if ($onlyNode === $child) {
                                    $matched = TRUE;
                                }
                            }

                            if (!$matched) {
                                break(2);
                            }
                        }
                    }

                    $filtered[] = $node;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return;
            }
        }

        // return the first node that matches all criteria
        return !empty($nodes) ? $nodes : array();
    }

    /**
     * Recursively get flat array of all descendants of this node.
     *
     * @param  DOMNode $node
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    protected static function getDescendants(DOMNode $node)
    {
        $allChildren = array();
        $childNodes  = $node->childNodes ? $node->childNodes : array();

        foreach ($childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE ||
                $child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            $children    = self::getDescendants($child);
            $allChildren = array_merge($allChildren, $children, array($child));
        }

        return isset($allChildren) ? $allChildren : array();
    }

    /**
     * Gets elements by case insensitive tagname.
     *
     * @param  DOMDocument $dom
     * @param  string      $tag
     * @return DOMNodeList
     * @since  Method available since Release 3.4.0
     */
    protected static function getElementsByCaseInsensitiveTagName(DOMDocument $dom, $tag)
    {
        $elements = $dom->getElementsByTagName(strtolower($tag));

        if ($elements->length == 0) {
            $elements = $dom->getElementsByTagName(strtoupper($tag));
        }

        return $elements;
    }

    /**
     * Get the text value of this node's child text node.
     *
     * @param  DOMNode $node
     * @return string
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    protected static function getNodeText(DOMNode $node)
    {
        if (!$node->childNodes instanceof DOMNodeList) {
            return '';
        }

        $result = '';

        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_TEXT_NODE) {
                $result .= trim($childNode->data) . ' ';
            } else {
                $result .= self::getNodeText($childNode);
            }
        }

        return str_replace('  ', ' ', $result);
    }
}
