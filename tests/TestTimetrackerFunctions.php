<?php
require_once("ptimetracker/ptimetracker.php");
require_once("vfsStream/vfsStream.php");

use ptimetracker\IFileWriter\VfsFileWriter;
use ptimetracker\IFileWriter;

class TestTimetrackerFunctions extends PHPUnit_Framework_TestCase {

  protected $writer;

  protected $sDataDir = "datadir";
  protected $sCurrentPath = "datadir/current";
  protected $sLastPath = "datadir/last";

  protected function setUp() {
    $this->writer = new VfsFileWriter($this->sDataDir);
  }

  protected function tearDown() {
    unset($this->writer);
  }

  public function testPathSegmentsGetConcatenatedCorrectly(){
     $result = ptimetracker\concat_path("a", "b", "c");
     $this->assertEquals("a/b/c", $result);
  }

  public function testSlashesInPathSegmentsAreHandledCorrectly(){
     $result = ptimetracker\concat_path("a/", "b/", "c/");
     $this->assertEquals("a/b/c/", $result);
  }

  public function testStripLastStripsLastChar(){
    $result = ptimetracker\strip_last("abccc", "c");
    $this->assertEquals("abcc", $result);
  }

  public function testStripLastDoesntStripWrongChar(){
    $result = ptimetracker\strip_last("abccc", "x");
    $this->assertEquals("abccc", $result);
  }

  public function testTaskReturnsNullWhenNoCurrentTaskIsActive(){
    $currentTask = ptimetracker\task("current", $this->sDataDir, $this->writer);
    $this->assertNull($currentTask, "current Task is not null as expected");
  }

  public function testTaskReturnsNullWhenCurrentFileIsEmpty(){
    fclose(fopen(vfsStream::url($this->sCurrentPath), "a"));
    $currentTask = ptimetracker\task("current", $this->sDataDir, $this->writer);
    $this->assertNull($currentTask, "current empty Task is not null as expected");
  }

  public function testTaskReturnsCurrentArrayWhenCurrentTaskIsActive(){
    $start = time()-100;
    $this->writer->writeToFile($this->sCurrentPath, $start . "\ttesttask", false);
    $currentTask = ptimetracker\task("current", $this->sDataDir, $this->writer);
    $this->assertEquals($currentTask["task"], "testtask");
  }

  public function testSetCurrentTaskSetsCurrentTask(){
    $this->assertFalse($this->writer->fileExists($this->sCurrentPath), "current path already exists");
    ptimetracker\set_current_task("testtask", $this->sLastPath, $this->sCurrentPath, $this->writer);
    $this->assertTrue($this->writer->fileExists($this->sCurrentPath), "current file wasn't created");
    $aLines = $this->writer->readFromFile($this->sCurrentPath);
    $sLine = $aLines[0];
    $aTaskParts = preg_split("/\t/", $sLine);
    $sTask = $aTaskParts[1];
    $this->assertEquals("testtask", $sTask, "wrong task was created");
  }

  public function testMinutesToClockString(){
    $this->assertEquals("1:01", ptimetracker\minutes_to_clock_string(61), "Minutes weren't converted to nice string correctly");
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testMinutesToClockStringWithNegativeNumber(){
    ptimetracker\minutes_to_clock_string(-10);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testMinutesToClockStringWithStringArgument(){
    ptimetracker\minutes_to_clock_string("invalid");
  }

  

}
