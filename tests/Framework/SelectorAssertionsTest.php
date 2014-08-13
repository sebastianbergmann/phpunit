<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Framework_SelectorAssertionsTest extends PHPUnit_Framework_TestCase
{
    private $html;

    protected function setUp()
    {
        $this->html = file_get_contents(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'SelectorAssertionsFixture.html'
        );
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeTrue()
    {
        $matcher = array('tag' => 'html');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagTypeFalse()
    {
        $matcher = array('tag' => 'code');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagIdTrue()
    {
        $matcher = array('id' => 'test_text');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagIdFalse()
    {
        $matcher = array('id' => 'test_text_doesnt_exist');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagStringContentTrue()
    {
        $matcher = array('id' => 'test_text',
            'content' => 'My test tag content');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagStringContentFalse()
    {
        $matcher = array('id' => 'test_text',
            'content' => 'My non existent tag content');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagRegexpContentTrue()
    {
        $matcher = array('id' => 'test_text',
            'content' => 'regexp:/test tag/');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagRegexpModifierContentTrue()
    {
        $matcher = array('id' => 'test_text',
            'content' => 'regexp:/TEST TAG/i');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagRegexpContentFalse()
    {
        $matcher = array('id' => 'test_text',
            'content' => 'regexp:/asdf/');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagCdataContentTrue()
    {
        $matcher = array('tag' => 'script',
            'content' => 'alert(\'Hello, world!\');');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagCdataontentFalse()
    {
        $matcher = array('tag' => 'script',
            'content' => 'asdf');
        $this->assertTag($matcher, $this->html);
    }



    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesTrueA()
    {
        $matcher = array('tag' => 'span',
            'attributes' => array('class' => 'test_class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesTrueB()
    {
        $matcher = array('tag' => 'div',
            'attributes' => array('id' => 'test_child_id'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesFalse()
    {
        $matcher = array('tag' => 'span',
            'attributes' => array('class' => 'test_missing_class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesRegexpTrueA()
    {
        $matcher = array('tag' => 'span',
            'attributes' => array('class' => 'regexp:/.+_class/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesRegexpTrueB()
    {
        $matcher = array('tag' => 'div',
            'attributes' => array('id' => 'regexp:/.+_child_.+/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesRegexpModifierTrue()
    {
        $matcher = array('tag' => 'div',
            'attributes' => array('id' => 'regexp:/.+_CHILD_.+/i'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesRegexpModifierFalse()
    {
        $matcher = array('tag' => 'div',
            'attributes' => array('id' => 'regexp:/.+_CHILD_.+/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesRegexpFalse()
    {
        $matcher = array('tag' => 'span',
            'attributes' => array('class' => 'regexp:/.+_missing_.+/'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesMultiPartClassTrueA()
    {
        $matcher = array('tag' => 'div',
            'id'  => 'test_multi_class',
            'attributes' => array('class' => 'multi class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAttributesMultiPartClassTrueB()
    {
        $matcher = array('tag' => 'div',
            'id'  => 'test_multi_class',
            'attributes' => array('class' => 'multi'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAttributesMultiPartClassFalse()
    {
        $matcher = array('tag' => 'div',
            'id'  => 'test_multi_class',
            'attributes' => array('class' => 'mul'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagParentTrue()
    {
        $matcher = array('tag' => 'head',
            'parent' => array('tag' => 'html'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagParentFalse()
    {
        $matcher = array('tag' => 'head',
            'parent' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagMultiplePossibleChildren()
    {
        $matcher = array(
            'tag' => 'li',
            'parent' => array(
                'tag' => 'ul',
                'id' => 'another_ul'
            )
        );
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildTrue()
    {
        $matcher = array('tag' => 'html',
            'child' => array('tag' => 'head'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildFalse()
    {
        $matcher = array('tag' => 'html',
            'child' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAdjacentSiblingTrue()
    {
        $matcher = array('tag' => 'img',
            'adjacent-sibling' => array('tag' => 'input'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAdjacentSiblingFalse()
    {
        $matcher = array('tag' => 'img',
            'adjacent-sibling' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }


    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagAncestorTrue()
    {
        $matcher = array('tag' => 'div',
            'ancestor' => array('tag' => 'html'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagAncestorFalse()
    {
        $matcher = array('tag' => 'html',
            'ancestor' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagDescendantTrue()
    {
        $matcher = array('tag' => 'html',
            'descendant' => array('tag' => 'div'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagDescendantFalse()
    {
        $matcher = array('tag' => 'div',
            'descendant' => array('tag' => 'html'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenCountTrue()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('count' => 3));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenCountFalse()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('count' => 5));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenLessThanTrue()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('less_than' => 10));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenLessThanFalse()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('less_than' => 2));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenGreaterThanTrue()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('greater_than' => 2));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenGreaterThanFalse()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('greater_than' => 10));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagChildrenOnlyTrue()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('only' => array('tag' =>'li')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagChildrenOnlyFalse()
    {
        $matcher = array('tag' => 'ul',
            'children' => array('only' => array('tag' =>'div')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeIdTrueA()
    {
        $matcher = array('tag' => 'ul', 'id' => 'my_ul');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeIdTrueB()
    {
        $matcher = array('id' => 'my_ul', 'tag' => 'ul');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagTypeIdTrueC()
    {
        $matcher = array('tag' => 'input', 'id'  => 'input_test_id');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertTagTypeIdFalse()
    {
        $matcher = array('tag' => 'div', 'id'  => 'my_ul');
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertTagContentAttributes()
    {
        $matcher = array('tag' => 'div',
            'content'    => 'Test Id Text',
            'attributes' => array('id' => 'test_id',
                'class' => 'my_test_class'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertParentContentAttributes()
    {
        $matcher = array('tag'        => 'div',
            'content'    => 'Test Id Text',
            'attributes' => array('id'    => 'test_id',
                'class' => 'my_test_class'),
            'parent'     => array('tag' => 'body'));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertChildContentAttributes()
    {
        $matcher = array('tag'        => 'div',
            'content'    => 'Test Id Text',
            'attributes' => array('id'    => 'test_id',
                'class' => 'my_test_class'),
            'child'      => array('tag'        => 'div',
                'attributes' => array('id' => 'test_child_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertAdjacentSiblingContentAttributes()
    {
        $matcher = array('tag'              => 'div',
            'content'          => 'Test Id Text',
            'attributes'       => array('id'    => 'test_id',
                'class' => 'my_test_class'),
            'adjacent-sibling' => array('tag'        => 'div',
                'attributes' => array('id' => 'test_children')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertChildSubChildren()
    {
        $matcher = array('id' => 'test_id',
            'child' => array('id' => 'test_child_id',
                'child' => array('id' => 'test_subchild_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertAdjacentSiblingSubAdjacentSibling()
    {
        $matcher = array('id' => 'test_id',
            'adjacent-sibling' => array('id' => 'test_children',
                'adjacent-sibling' => array('class' => 'test_class')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertAncestorContentAttributes()
    {
        $matcher = array('id'         => 'test_subchild_id',
            'content'    => 'My Subchild',
            'attributes' => array('id' => 'test_subchild_id'),
            'ancestor'   => array('tag'        => 'div',
                'attributes' => array('id' => 'test_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertDescendantContentAttributes()
    {
        $matcher = array('id'         => 'test_id',
            'content'    => 'Test Id Text',
            'attributes' => array('id'  => 'test_id'),
            'descendant' => array('tag'        => 'span',
                'attributes' => array('id' => 'test_subchild_id')));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     */
    public function testAssertChildrenContentAttributes()
    {
        $matcher = array('id'         => 'test_children',
            'content'    => 'My Children',
            'attributes' => array('class'  => 'children'),

            'children' => array('less_than'    => '25',
                'greater_than' => '2',
                'only'         => array('tag' => 'div',
                    'attributes' => array('class' => 'my_child'))
            ));
        $this->assertTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertTag
     * @ticket 1380
     */
    public function testAssertTagWithMalformedHTML()
    {
        $matcher = array(
            'tag' => 'form',
            'descendant' => array(
                'tag' => 'input',
                'attributes' => array(
                    'name' => 'property',
                    'value' => 'Foo'
                )
            )
        );

        $html = '<form><input name="property" value="Foo"><hr></hr></form>';
        $this->assertTag($matcher, $html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertNotTag
     */
    public function testAssertNotTagTypeIdFalse()
    {
        $matcher = array('tag' => 'div', 'id'  => 'my_ul');
        $this->assertNotTag($matcher, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertNotTag
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertNotTagContentAttributes()
    {
        $matcher = array('tag' => 'div',
            'content'    => 'Test Id Text',
            'attributes' => array('id' => 'test_id',
                'class' => 'my_test_class'));
        $this->assertNotTag($matcher, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountPresentTrue()
    {
        $selector = 'div#test_id';
        $count    = true;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountPresentFalse()
    {
        $selector = 'div#non_existent';
        $count    = true;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountNotPresentTrue()
    {
        $selector = 'div#non_existent';
        $count    = false;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectNotPresentFalse()
    {
        $selector = 'div#test_id';
        $count    = false;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountChildTrue()
    {
        $selector = '#my_ul > li';
        $count    = 3;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountChildFalse()
    {
        $selector = '#my_ul > li';
        $count    = 4;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountAdjacentSiblingTrue()
    {
        $selector = 'div + div + div';
        $count    = 2;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountAdjacentSiblingFalse()
    {
        $selector = '#test_children + div';
        $count    = 1;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountDescendantTrue()
    {
        $selector = '#my_ul li';
        $count    = 3;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountDescendantFalse()
    {
        $selector = '#my_ul li';
        $count    = 4;

        $this->assertSelectCount($selector, $count, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountGreaterThanTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 2);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountGreaterThanFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountGreaterThanEqualToTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('>=' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountGreaterThanEqualToFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('>=' => 4);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountLessThanTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('<' => 4);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountLessThanFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('<' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountLessThanEqualToTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('<=' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountLessThanEqualToFalse()
    {
        $selector = '#my_ul > li';
        $range  = array('<=' => 2);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectCount
     */
    public function testAssertSelectCountRangeTrue()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 2, '<' => 4);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectCount
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectCountRangeFalse()
    {
        $selector = '#my_ul > li';
        $range    = array('>' => 1, '<' => 3);

        $this->assertSelectCount($selector, $range, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectEquals
     */
    public function testAssertSelectEqualsContentPresentTrue()
    {
        $selector = 'span.test_class';
        $content  = 'Test Class Text';

        $this->assertSelectEquals($selector, $content, true, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectEquals
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectEqualsContentPresentFalse()
    {
        $selector = 'span.test_class';
        $content  = 'Test Nonexistent';

        $this->assertSelectEquals($selector, $content, true, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectEquals
     */
    public function testAssertSelectEqualsContentNotPresentTrue()
    {
        $selector = 'span.test_class';
        $content  = 'Test Nonexistent';

        $this->assertSelectEquals($selector, $content, false, $this->html);
    }

    /**
     * @covers            PHPUnit_Framework_Assert::assertSelectEquals
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertSelectEqualsContentNotPresentFalse()
    {
        $selector = 'span.test_class';
        $content  = 'Test Class Text';

        $this->assertSelectEquals($selector, $content, false, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectRegExp
     */
    public function testAssertSelectRegExpContentPresentTrue()
    {
        $selector = 'span.test_class';
        $regexp   = '/Test.*Text/';

        $this->assertSelectRegExp($selector, $regexp, true, $this->html);
    }

    /**
     * @covers PHPUnit_Framework_Assert::assertSelectRegExp
     */
    public function testAssertSelectRegExpContentPresentFalse()
    {
        $selector = 'span.test_class';
        $regexp   = '/Nonexistant/';

        $this->assertSelectRegExp($selector, $regexp, false, $this->html);
    }
}
