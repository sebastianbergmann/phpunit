<?
class ParallelismTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->filename = 'parallelism_test_tempfile.tmp';
    }
    
    function test_simultest_one() {
        while (file_get_contents($this->filename) != '1') { usleep(10000); }
        file_put_contents($this->filename, '2');
        while (file_get_contents($this->filename) != '3') { usleep(10000); }
        unlink($this->filename);
    }
        
    function test_simultest_two() {
        file_put_contents($this->filename, '1');
        while (file_get_contents($this->filename) != '2') { usleep(10000); }
        file_put_contents($this->filename, '3');
        while (file_exists($this->filename)) { usleep(10000); }
    }
    
}
