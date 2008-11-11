--TEST--
phpunit --coverage-source /tmp BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$tempPath = dirname(__FILE__);

$_SERVER['argv'][1] = '--coverage-source';
$_SERVER['argv'][2] = $tempPath;
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

define('PHPUnit_MAIN_METHOD', '');
require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';

PHPUnit_TextUI_Command::main(FALSE);

print file_get_contents($tempPath . DIRECTORY_SEPARATOR . 'BankAccount.php.xml');
print file_get_contents($tempPath . DIRECTORY_SEPARATOR . 'BankAccountTest.php.xml');
?>
--CLEAN--
<?php
$tempPath = dirname(__FILE__);
unlink($tempPath . DIRECTORY_SEPARATOR . 'BankAccount.php.xml');
unlink($tempPath . DIRECTORY_SEPARATOR . 'BankAccountTest.php.xml');
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...

Time: 0 seconds

OK (3 tests, 3 assertions)

Writing code coverage data to XML files, this may take a moment.
<?xml version="1.0" encoding="UTF-8"?>
<coveredFile fullPath="%s/BankAccount.php" shortenedPath="BankAccount.php" generated="%i" phpunit="%s">
  <line lineNumber="1" executed="-3">
    <body>&lt;?php</body>
  </line>
  <line lineNumber="2" executed="-3">
    <body>/**</body>
  </line>
  <line lineNumber="3" executed="-3">
    <body> * PHPUnit</body>
  </line>
  <line lineNumber="4" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="5" executed="-3">
    <body> * Copyright (c) 2002-2008, Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;.</body>
  </line>
  <line lineNumber="6" executed="-3">
    <body> * All rights reserved.</body>
  </line>
  <line lineNumber="7" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="8" executed="-3">
    <body> * Redistribution and use in source and binary forms, with or without</body>
  </line>
  <line lineNumber="9" executed="-3">
    <body> * modification, are permitted provided that the following conditions</body>
  </line>
  <line lineNumber="10" executed="-3">
    <body> * are met:</body>
  </line>
  <line lineNumber="11" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="12" executed="-3">
    <body> *   * Redistributions of source code must retain the above copyright</body>
  </line>
  <line lineNumber="13" executed="-3">
    <body> *     notice, this list of conditions and the following disclaimer.</body>
  </line>
  <line lineNumber="14" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="15" executed="-3">
    <body> *   * Redistributions in binary form must reproduce the above copyright</body>
  </line>
  <line lineNumber="16" executed="-3">
    <body> *     notice, this list of conditions and the following disclaimer in</body>
  </line>
  <line lineNumber="17" executed="-3">
    <body> *     the documentation and/or other materials provided with the</body>
  </line>
  <line lineNumber="18" executed="-3">
    <body> *     distribution.</body>
  </line>
  <line lineNumber="19" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="20" executed="-3">
    <body> *   * Neither the name of Sebastian Bergmann nor the names of his</body>
  </line>
  <line lineNumber="21" executed="-3">
    <body> *     contributors may be used to endorse or promote products derived</body>
  </line>
  <line lineNumber="22" executed="-3">
    <body> *     from this software without specific prior written permission.</body>
  </line>
  <line lineNumber="23" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="24" executed="-3">
    <body> * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS</body>
  </line>
  <line lineNumber="25" executed="-3">
    <body> * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT</body>
  </line>
  <line lineNumber="26" executed="-3">
    <body> * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS</body>
  </line>
  <line lineNumber="27" executed="-3">
    <body> * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE</body>
  </line>
  <line lineNumber="28" executed="-3">
    <body> * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,</body>
  </line>
  <line lineNumber="29" executed="-3">
    <body> * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,</body>
  </line>
  <line lineNumber="30" executed="-3">
    <body> * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;</body>
  </line>
  <line lineNumber="31" executed="-3">
    <body> * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER</body>
  </line>
  <line lineNumber="32" executed="-3">
    <body> * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT</body>
  </line>
  <line lineNumber="33" executed="-3">
    <body> * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN</body>
  </line>
  <line lineNumber="34" executed="-3">
    <body> * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE</body>
  </line>
  <line lineNumber="35" executed="-3">
    <body> * POSSIBILITY OF SUCH DAMAGE.</body>
  </line>
  <line lineNumber="36" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="37" executed="-3">
    <body> * @category   Testing</body>
  </line>
  <line lineNumber="38" executed="-3">
    <body> * @package    PHPUnit</body>
  </line>
  <line lineNumber="39" executed="-3">
    <body> * @author     Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="40" executed="-3">
    <body> * @copyright  2002-2008 Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="41" executed="-3">
    <body> * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License</body>
  </line>
  <line lineNumber="42" executed="-3">
    <body> * @version    SVN: $Id%s$</body>
  </line>
  <line lineNumber="43" executed="-3">
    <body> * @link       http://www.phpunit.de/</body>
  </line>
  <line lineNumber="44" executed="-3">
    <body> * @since      File available since Release 2.3.0</body>
  </line>
  <line lineNumber="45" executed="-3">
    <body> */</body>
  </line>
  <line lineNumber="46" executed="-3">
    <body></body>
  </line>
  <line lineNumber="47" executed="-3">
    <body>class BankAccountException extends RuntimeException {}</body>
  </line>
  <line lineNumber="48" executed="-3">
    <body></body>
  </line>
  <line lineNumber="49" executed="-3">
    <body>/**</body>
  </line>
  <line lineNumber="50" executed="-3">
    <body> * A bank account.</body>
  </line>
  <line lineNumber="51" executed="-3">
    <body> *</body>
  </line>
  <line lineNumber="52" executed="-3">
    <body> * @category   Testing</body>
  </line>
  <line lineNumber="53" executed="-3">
    <body> * @package    PHPUnit</body>
  </line>
  <line lineNumber="54" executed="-3">
    <body> * @author     Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="55" executed="-3">
    <body> * @copyright  2002-2008 Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="56" executed="-3">
    <body> * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License</body>
  </line>
  <line lineNumber="57" executed="-3">
    <body> * @version    Release: %s</body>
  </line>
  <line lineNumber="58" executed="-3">
    <body> * @link       http://www.phpunit.de/</body>
  </line>
  <line lineNumber="59" executed="-3">
    <body> * @since      Class available since Release 2.3.0</body>
  </line>
  <line lineNumber="60" executed="-3">
    <body> */</body>
  </line>
  <line lineNumber="61" executed="-3">
    <body>class BankAccount</body>
  </line>
  <line lineNumber="62" executed="-3">
    <body>{</body>
  </line>
  <line lineNumber="63" executed="-3">
    <body>    /**</body>
  </line>
  <line lineNumber="64" executed="-3">
    <body>     * The bank account's balance.</body>
  </line>
  <line lineNumber="65" executed="-3">
    <body>     *</body>
  </line>
  <line lineNumber="66" executed="-3">
    <body>     * @var    float</body>
  </line>
  <line lineNumber="67" executed="-3">
    <body>     */</body>
  </line>
  <line lineNumber="68" executed="-3">
    <body>    protected $balance = 0;</body>
  </line>
  <line lineNumber="69" executed="-3">
    <body></body>
  </line>
  <line lineNumber="70" executed="-3">
    <body>    /**</body>
  </line>
  <line lineNumber="71" executed="-3">
    <body>     * Returns the bank account's balance.</body>
  </line>
  <line lineNumber="72" executed="-3">
    <body>     *</body>
  </line>
  <line lineNumber="73" executed="-3">
    <body>     * @return float</body>
  </line>
  <line lineNumber="74" executed="-3">
    <body>     */</body>
  </line>
  <line lineNumber="75" executed="-3">
    <body>    public function getBalance()</body>
  </line>
  <line lineNumber="76" executed="-3">
    <body>    {</body>
  </line>
  <line lineNumber="77" executed="1">
    <body>        return $this-&gt;balance;</body>
    <tests>
      <test name="testBalanceIsInitiallyZero" status="0" class="BankAccountTest" fullPath="%s/BankAccountTest.php" shortenedPath="/BankAccountTest.php" line="76"/>
    </tests>
  </line>
  <line lineNumber="78" executed="-2">
    <body>    }</body>
  </line>
  <line lineNumber="79" executed="-3">
    <body></body>
  </line>
  <line lineNumber="80" executed="-3">
    <body>    /**</body>
  </line>
  <line lineNumber="81" executed="-3">
    <body>     * Sets the bank account's balance.</body>
  </line>
  <line lineNumber="82" executed="-3">
    <body>     *</body>
  </line>
  <line lineNumber="83" executed="-3">
    <body>     * @param  float $balance</body>
  </line>
  <line lineNumber="84" executed="-3">
    <body>     * @throws BankAccountException</body>
  </line>
  <line lineNumber="85" executed="-3">
    <body>     */</body>
  </line>
  <line lineNumber="86" executed="-3">
    <body>    protected function setBalance($balance)</body>
  </line>
  <line lineNumber="87" executed="-3">
    <body>    {</body>
  </line>
  <line lineNumber="88" executed="-1">
    <body>        if ($balance &gt;= 0) {</body>
  </line>
  <line lineNumber="89" executed="-1">
    <body>            $this-&gt;balance = $balance;</body>
  </line>
  <line lineNumber="90" executed="-1">
    <body>        } else {</body>
  </line>
  <line lineNumber="91" executed="-1">
    <body>            throw new BankAccountException;</body>
  </line>
  <line lineNumber="92" executed="-3">
    <body>        }</body>
  </line>
  <line lineNumber="93" executed="-1">
    <body>    }</body>
  </line>
  <line lineNumber="94" executed="-3">
    <body></body>
  </line>
  <line lineNumber="95" executed="-3">
    <body>    /**</body>
  </line>
  <line lineNumber="96" executed="-3">
    <body>     * Deposits an amount of money to the bank account.</body>
  </line>
  <line lineNumber="97" executed="-3">
    <body>     *</body>
  </line>
  <line lineNumber="98" executed="-3">
    <body>     * @param  float $balance</body>
  </line>
  <line lineNumber="99" executed="-3">
    <body>     * @throws BankAccountException</body>
  </line>
  <line lineNumber="100" executed="-3">
    <body>     */</body>
  </line>
  <line lineNumber="101" executed="-3">
    <body>    public function depositMoney($balance)</body>
  </line>
  <line lineNumber="102" executed="-3">
    <body>    {</body>
  </line>
  <line lineNumber="103" executed="1">
    <body>        $this-&gt;setBalance($this-&gt;getBalance() + $balance);</body>
    <tests>
      <test name="testBalanceCannotBecomeNegative2" status="0" class="BankAccountTest" fullPath="%s/BankAccountTest.php" shortenedPath="/BankAccountTest.php" line="106"/>
    </tests>
  </line>
  <line lineNumber="104" executed="-3">
    <body></body>
  </line>
  <line lineNumber="105" executed="-1">
    <body>        return $this-&gt;getBalance();</body>
  </line>
  <line lineNumber="106" executed="-2">
    <body>    }</body>
  </line>
  <line lineNumber="107" executed="-3">
    <body></body>
  </line>
  <line lineNumber="108" executed="-3">
    <body>    /**</body>
  </line>
  <line lineNumber="109" executed="-3">
    <body>     * Withdraws an amount of money from the bank account.</body>
  </line>
  <line lineNumber="110" executed="-3">
    <body>     *</body>
  </line>
  <line lineNumber="111" executed="-3">
    <body>     * @param  float $balance</body>
  </line>
  <line lineNumber="112" executed="-3">
    <body>     * @throws BankAccountException</body>
  </line>
  <line lineNumber="113" executed="-3">
    <body>     */</body>
  </line>
  <line lineNumber="114" executed="-3">
    <body>    public function withdrawMoney($balance)</body>
  </line>
  <line lineNumber="115" executed="-3">
    <body>    {</body>
  </line>
  <line lineNumber="116" executed="1">
    <body>        $this-&gt;setBalance($this-&gt;getBalance() - $balance);</body>
    <tests>
      <test name="testBalanceCannotBecomeNegative" status="0" class="BankAccountTest" fullPath="%s/BankAccountTest.php" shortenedPath="/BankAccountTest.php" line="86"/>
    </tests>
  </line>
  <line lineNumber="117" executed="-3">
    <body></body>
  </line>
  <line lineNumber="118" executed="-1">
    <body>        return $this-&gt;getBalance();</body>
  </line>
  <line lineNumber="119" executed="-2">
    <body>    }</body>
  </line>
  <line lineNumber="120" executed="-3">
    <body>}</body>
  </line>
  <line lineNumber="121" executed="-3">
    <body>?&gt;</body>
  </line>
</coveredFile>
<?xml version="1.0" encoding="UTF-8"?>
<testFile fullPath="%s/BankAccountTest.php" shortenedPath="BankAccountTest.php" generated="%i" phpunit="%s">
  <line lineNumber="1">
    <body>&lt;?php</body>
  </line>
  <line lineNumber="2">
    <body>/**</body>
  </line>
  <line lineNumber="3">
    <body> * PHPUnit</body>
  </line>
  <line lineNumber="4">
    <body> *</body>
  </line>
  <line lineNumber="5">
    <body> * Copyright (c) 2002-2008, Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;.</body>
  </line>
  <line lineNumber="6">
    <body> * All rights reserved.</body>
  </line>
  <line lineNumber="7">
    <body> *</body>
  </line>
  <line lineNumber="8">
    <body> * Redistribution and use in source and binary forms, with or without</body>
  </line>
  <line lineNumber="9">
    <body> * modification, are permitted provided that the following conditions</body>
  </line>
  <line lineNumber="10">
    <body> * are met:</body>
  </line>
  <line lineNumber="11">
    <body> *</body>
  </line>
  <line lineNumber="12">
    <body> *   * Redistributions of source code must retain the above copyright</body>
  </line>
  <line lineNumber="13">
    <body> *     notice, this list of conditions and the following disclaimer.</body>
  </line>
  <line lineNumber="14">
    <body> *</body>
  </line>
  <line lineNumber="15">
    <body> *   * Redistributions in binary form must reproduce the above copyright</body>
  </line>
  <line lineNumber="16">
    <body> *     notice, this list of conditions and the following disclaimer in</body>
  </line>
  <line lineNumber="17">
    <body> *     the documentation and/or other materials provided with the</body>
  </line>
  <line lineNumber="18">
    <body> *     distribution.</body>
  </line>
  <line lineNumber="19">
    <body> *</body>
  </line>
  <line lineNumber="20">
    <body> *   * Neither the name of Sebastian Bergmann nor the names of his</body>
  </line>
  <line lineNumber="21">
    <body> *     contributors may be used to endorse or promote products derived</body>
  </line>
  <line lineNumber="22">
    <body> *     from this software without specific prior written permission.</body>
  </line>
  <line lineNumber="23">
    <body> *</body>
  </line>
  <line lineNumber="24">
    <body> * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS</body>
  </line>
  <line lineNumber="25">
    <body> * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT</body>
  </line>
  <line lineNumber="26">
    <body> * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS</body>
  </line>
  <line lineNumber="27">
    <body> * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE</body>
  </line>
  <line lineNumber="28">
    <body> * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,</body>
  </line>
  <line lineNumber="29">
    <body> * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,</body>
  </line>
  <line lineNumber="30">
    <body> * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;</body>
  </line>
  <line lineNumber="31">
    <body> * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER</body>
  </line>
  <line lineNumber="32">
    <body> * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT</body>
  </line>
  <line lineNumber="33">
    <body> * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN</body>
  </line>
  <line lineNumber="34">
    <body> * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE</body>
  </line>
  <line lineNumber="35">
    <body> * POSSIBILITY OF SUCH DAMAGE.</body>
  </line>
  <line lineNumber="36">
    <body> *</body>
  </line>
  <line lineNumber="37">
    <body> * @category   Testing</body>
  </line>
  <line lineNumber="38">
    <body> * @package    PHPUnit</body>
  </line>
  <line lineNumber="39">
    <body> * @author     Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="40">
    <body> * @copyright  2002-2008 Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="41">
    <body> * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License</body>
  </line>
  <line lineNumber="42">
    <body> * @version    SVN: $Id%s$</body>
  </line>
  <line lineNumber="43">
    <body> * @link       http://www.phpunit.de/</body>
  </line>
  <line lineNumber="44">
    <body> * @since      File available since Release 2.3.0</body>
  </line>
  <line lineNumber="45">
    <body> */</body>
  </line>
  <line lineNumber="46">
    <body></body>
  </line>
  <line lineNumber="47">
    <body>require_once 'PHPUnit/Framework/TestCase.php';</body>
  </line>
  <line lineNumber="48">
    <body>require_once 'BankAccount.php';</body>
  </line>
  <line lineNumber="49">
    <body></body>
  </line>
  <line lineNumber="50">
    <body>/**</body>
  </line>
  <line lineNumber="51">
    <body> * Tests for the BankAccount class.</body>
  </line>
  <line lineNumber="52">
    <body> *</body>
  </line>
  <line lineNumber="53">
    <body> * @category   Testing</body>
  </line>
  <line lineNumber="54">
    <body> * @package    PHPUnit</body>
  </line>
  <line lineNumber="55">
    <body> * @author     Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="56">
    <body> * @copyright  2002-2008 Sebastian Bergmann &lt;sb@sebastian-bergmann.de&gt;</body>
  </line>
  <line lineNumber="57">
    <body> * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License</body>
  </line>
  <line lineNumber="58">
    <body> * @version    Release: %s</body>
  </line>
  <line lineNumber="59">
    <body> * @link       http://www.phpunit.de/</body>
  </line>
  <line lineNumber="60">
    <body> * @since      Class available since Release 2.3.0</body>
  </line>
  <line lineNumber="61">
    <body> */</body>
  </line>
  <line lineNumber="62">
    <body>class BankAccountTest extends PHPUnit_Framework_TestCase</body>
  </line>
  <line lineNumber="63">
    <body>{</body>
  </line>
  <line lineNumber="64">
    <body>    protected $ba;</body>
  </line>
  <line lineNumber="65">
    <body></body>
  </line>
  <line lineNumber="66">
    <body>    protected function setUp()</body>
  </line>
  <line lineNumber="67">
    <body>    {</body>
  </line>
  <line lineNumber="68">
    <body>        $this-&gt;ba = new BankAccount;</body>
  </line>
  <line lineNumber="69">
    <body>    }</body>
  </line>
  <line lineNumber="70">
    <body></body>
  </line>
  <line lineNumber="71">
    <body>    /**</body>
  </line>
  <line lineNumber="72">
    <body>     * @covers BankAccount::getBalance</body>
  </line>
  <line lineNumber="73">
    <body>     * @group balanceIsInitiallyZero</body>
  </line>
  <line lineNumber="74">
    <body>     * @group specification</body>
  </line>
  <line lineNumber="75">
    <body>     */</body>
  </line>
  <line lineNumber="76">
    <body>    public function testBalanceIsInitiallyZero()</body>
    <coveredFiles>
      <coveredFile fullPath="%s/BankAccountTest.php" shortenedPath="BankAccount.php">
        <coveredLine>77</coveredLine>
      </coveredFile>
    </coveredFiles>
  </line>
  <line lineNumber="77">
    <body>    {</body>
  </line>
  <line lineNumber="78">
    <body>        $this-&gt;assertEquals(0, $this-&gt;ba-&gt;getBalance());</body>
  </line>
  <line lineNumber="79">
    <body>    }</body>
  </line>
  <line lineNumber="80">
    <body></body>
  </line>
  <line lineNumber="81">
    <body>    /**</body>
  </line>
  <line lineNumber="82">
    <body>     * @covers BankAccount::withdrawMoney</body>
  </line>
  <line lineNumber="83">
    <body>     * @group balanceCannotBecomeNegative</body>
  </line>
  <line lineNumber="84">
    <body>     * @group specification</body>
  </line>
  <line lineNumber="85">
    <body>     */</body>
  </line>
  <line lineNumber="86">
    <body>    public function testBalanceCannotBecomeNegative()</body>
    <coveredFiles>
      <coveredFile fullPath="%s/BankAccountTest.php" shortenedPath="BankAccount.php">
        <coveredLine>116</coveredLine>
      </coveredFile>
    </coveredFiles>
  </line>
  <line lineNumber="87">
    <body>    {</body>
  </line>
  <line lineNumber="88">
    <body>        try {</body>
  </line>
  <line lineNumber="89">
    <body>            $this-&gt;ba-&gt;withdrawMoney(1);</body>
  </line>
  <line lineNumber="90">
    <body>        }</body>
  </line>
  <line lineNumber="91">
    <body></body>
  </line>
  <line lineNumber="92">
    <body>        catch (BankAccountException $e) {</body>
  </line>
  <line lineNumber="93">
    <body>            $this-&gt;assertEquals(0, $this-&gt;ba-&gt;getBalance());</body>
  </line>
  <line lineNumber="94">
    <body></body>
  </line>
  <line lineNumber="95">
    <body>            return;</body>
  </line>
  <line lineNumber="96">
    <body>        }</body>
  </line>
  <line lineNumber="97">
    <body></body>
  </line>
  <line lineNumber="98">
    <body>        $this-&gt;fail();</body>
  </line>
  <line lineNumber="99">
    <body>    }</body>
  </line>
  <line lineNumber="100">
    <body></body>
  </line>
  <line lineNumber="101">
    <body>    /**</body>
  </line>
  <line lineNumber="102">
    <body>     * @covers BankAccount::depositMoney</body>
  </line>
  <line lineNumber="103">
    <body>     * @group balanceCannotBecomeNegative</body>
  </line>
  <line lineNumber="104">
    <body>     * @group specification</body>
  </line>
  <line lineNumber="105">
    <body>     */</body>
  </line>
  <line lineNumber="106">
    <body>    public function testBalanceCannotBecomeNegative2()</body>
    <coveredFiles>
      <coveredFile fullPath="%s/BankAccountTest.php" shortenedPath="BankAccount.php">
        <coveredLine>103</coveredLine>
      </coveredFile>
    </coveredFiles>
  </line>
  <line lineNumber="107">
    <body>    {</body>
  </line>
  <line lineNumber="108">
    <body>        try {</body>
  </line>
  <line lineNumber="109">
    <body>            $this-&gt;ba-&gt;depositMoney(-1);</body>
  </line>
  <line lineNumber="110">
    <body>        }</body>
  </line>
  <line lineNumber="111">
    <body></body>
  </line>
  <line lineNumber="112">
    <body>        catch (BankAccountException $e) {</body>
  </line>
  <line lineNumber="113">
    <body>            $this-&gt;assertEquals(0, $this-&gt;ba-&gt;getBalance());</body>
  </line>
  <line lineNumber="114">
    <body></body>
  </line>
  <line lineNumber="115">
    <body>            return;</body>
  </line>
  <line lineNumber="116">
    <body>        }</body>
  </line>
  <line lineNumber="117">
    <body></body>
  </line>
  <line lineNumber="118">
    <body>        $this-&gt;fail();</body>
  </line>
  <line lineNumber="119">
    <body>    }</body>
  </line>
  <line lineNumber="120">
    <body></body>
  </line>
  <line lineNumber="121">
    <body>    /**</body>
  </line>
  <line lineNumber="122">
    <body>     * @covers BankAccount::getBalance</body>
  </line>
  <line lineNumber="123">
    <body>     * @covers BankAccount::depositMoney</body>
  </line>
  <line lineNumber="124">
    <body>     * @covers BankAccount::withdrawMoney</body>
  </line>
  <line lineNumber="125">
    <body>     * @group balanceCannotBecomeNegative</body>
  </line>
  <line lineNumber="126">
    <body>     */</body>
  </line>
  <line lineNumber="127">
    <body>/*</body>
  </line>
  <line lineNumber="128">
    <body>    public function testDepositWithdrawMoney()</body>
  </line>
  <line lineNumber="129">
    <body>    {</body>
  </line>
  <line lineNumber="130">
    <body>        $this-&gt;assertEquals(0, $this-&gt;ba-&gt;getBalance());</body>
  </line>
  <line lineNumber="131">
    <body>        $this-&gt;ba-&gt;depositMoney(1);</body>
  </line>
  <line lineNumber="132">
    <body>        $this-&gt;assertEquals(1, $this-&gt;ba-&gt;getBalance());</body>
  </line>
  <line lineNumber="133">
    <body>        $this-&gt;ba-&gt;withdrawMoney(1);</body>
  </line>
  <line lineNumber="134">
    <body>        $this-&gt;assertEquals(0, $this-&gt;ba-&gt;getBalance());</body>
  </line>
  <line lineNumber="135">
    <body>    }</body>
  </line>
  <line lineNumber="136">
    <body>*/</body>
  </line>
  <line lineNumber="137">
    <body>}</body>
  </line>
  <line lineNumber="138">
    <body>?&gt;</body>
  </line>
</testFile>
