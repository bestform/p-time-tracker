<?php
namespace ptimetracker\IFileWriter;
use ptimetracker\IFileWriter;

require_once("vfsStream/vfsStream.php");

class VfsFileWriter implements IFileWriter {

  public function __construct($sRoot){
    \vfsStream::setup($sRoot);
  }

  public function writeToFile($sPath, $sContents, $bAppend){
    $sWriteflag = $bAppend ? "a" : "w";
    if(!\vfsStreamWrapper::getRoot()->hasChild($sPath)){
      \vfsStreamWrapper::getRoot()->addChild(\vfsStream::newFile($sPath, 777));
    }
    $fFile = fopen(\vfsStream::url($sPath), $sWriteflag);
    fwrite($fFile, $sContents);
    fclose($fFile);
  }

  public function readFromFile($sPath) {
    return file(\vfsStream::url($sPath));
  }

  public function removeFile($sPath) {
    if(\vfsStreamWrapper::getRoot()->hasChild($sPath)){
      unlink(\vfsStream::url($sPath));
    }
  }

  public function fileExists($sPath) {
    return \vfsStreamWrapper::getRoot()->hasChild($sPath);
  }

  public function mkdir($sPath) {
    mkdir(\vfsStream::url($sPath));

  }

  public function rename($sOldName, $sNewName) {
    \vfsStreamWrapper::getRoot()->getChild($sOldName)->rename($sNewName);
  }
}
