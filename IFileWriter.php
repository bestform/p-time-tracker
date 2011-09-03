<?php

interface IFileWriter {

  public function writeToFile($sPath, $sContents, $bAppend);

  public function readFromFile($sPath);

  public function removeFile($sPath);

  public function fileExists($sPath);

  public function mkdir($sPath);

  public function rename($sOldName, $sNewName);

}
