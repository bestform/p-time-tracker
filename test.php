<?php
$GLOBALS["ptimetrackerTestrun"] = true;
require_once("ptimetracker.php");
unset($GLOBALS["ptimetrackerTestrun"]);
require_once("vfsStream/vfsStream.php");
require_once("VfsFileWriter.php");

class TestTimetrackerFunctions extends PHPUnit_Framework_TestCase {

    protected $writer;

    protected $sDataDir = "datadir";

    protected function setUp() {
      $this->writer = new VfsFileWriter($this->sDataDir);
    }

    protected function tearDown() {
      unset($this->writer);
    }

    public function testPathSegmentsGetConcatenatedCorrectly(){
       $result = concat_path("a", "b", "c");
       $this->assertEquals("a/b/c", $result);
    }

    public function testSlashesInPathSegmentsAreHandledCorrectly(){
       $result = concat_path("a/", "b/", "c/");
       $this->assertEquals("a/b/c/", $result);
    }

    public function testStripLastStripsLastChar(){
      $result = strip_last("abccc", "c");
      $this->assertEquals("abcc", $result);
    }

    public function testStripLastDoesntStripWrongChar(){
      $result = strip_last("abccc", "x");
      $this->assertEquals("abccc", $result);
    }

    public function testTaskReturnsNullWhenNoCurrentTaskIsActive(){
      $currentTask = task("current", $this->sDataDir, $this->writer);
      $this->assertNull($currentTask, "current Task is not null as expected");
    }

    public function testTaskReturnsNullWhenCurrentFileIsEmpty(){
      fclose(fopen(vfsStream::url(concat_path($this->sDataDir, "current")), "a"));
      $currentTask = task("current", $this->sDataDir, $this->writer);
      $this->assertNull($currentTask, "current empty Task is not null as expected");
    }

    public function testTaskReturnsCorrentArrayWhenCurrentTaskIsActive(){
      $start = time()-100;
      $this->writer->writeToFile(concat_path($this->sDataDir, "current"), $start . "\ttesttask", false);
      $currentTask = task("current", $this->sDataDir, $this->writer);
      $this->assertEquals($currentTask["task"], "testtask");
    }
    
}
