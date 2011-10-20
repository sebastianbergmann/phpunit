<?
/**
 * @runTestsInParallel 2
 */
class ParallelismAnnotationTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->filename = __class__.'tempfile.tmp';
    }

    function wait_for_contents($contents) {
        $time = time();
        while (file_get_contents($this->filename) != $contents) {
            usleep(10000);
            if (time() - $time > 2) {
                $this->fail("Other parallel test never wrote $contents to {$this->filename}");
            }
        }
    }
    
    function test_simultest_one() {
        $time = time();
        while (!file_exists($this->filename)) {
            usleep(10000);
            if (time() - $time > 2) {
                $this->fail("Other parallel test never created {$this->filename}");
            }
        }
        $this->wait_for_contents('1');
        file_put_contents($this->filename, '2');
        $this->wait_for_contents('3');
        unlink($this->filename);
    }
        
    function test_simultest_two() {
        file_put_contents($this->filename, '1');
        $this->wait_for_contents('2');
        file_put_contents($this->filename, '3');
    }
    
}
