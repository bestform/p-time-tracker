<?php

namespace ptimetracker\IFileWriter;
use ptimetracker\IFileWriter;

class FSFileWriter implements IFileWriter{

  public function writeToFile($sPath, $sContents, $bAppend) {
    $sWriteflag = $bAppend ? "a" : "w";
    $fFile = fopen($sPath, $sWriteflag);
    fwrite($fFile, $sContents);
    fclose($fFile);
  }

  public function removeFile($sPath) {
    if(file_exists($sPath)){
      unlink($sPath);
    }
  }

  public function fileExists($sPath) {
    return file_exists($sPath);
  }

  public function readFromFile($sPath) {
    return file($sPath);
  }

  public function mkdir($sPath) {
    mkdir($sPath);
  }

  public function rename($sOldName, $sNewName) {
    rename($sOldName, $sNewName);
  }
}
