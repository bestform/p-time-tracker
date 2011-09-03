<?php

date_default_timezone_set("Europe/Berlin");
$sDatadir = concat_path($_SERVER['HOME'], ".ptimetracker");
$sLastpath = concat_path($sDatadir, "last");
$sCurrentpath = concat_path($sDatadir, "current");

if(!file_exists($sDatadir)) {
  mkdir($sDatadir);
}

unset($argv[0]);
$sInput = join(" ", $argv);

/*
 * @return string the concatenated string
 */
function concat_path(){
  $sSep = '/';
  $aArgs = func_get_args();
  $sRet = array_pop($aArgs);
  while(count($aArgs) > 0){
    $sRet = strip_last(array_pop($aArgs), $sSep) . $sSep . $sRet;
  }
  return $sRet;
}

/*
 * @param string $sHaystack
 * @param string $sNeedle
 * @return string stripped string
 */

function strip_last($sHaystack, $sNeedle){
  if(substr($sHaystack, -1) == $sNeedle){
    return substr($sHaystack, 0, strlen($sHaystack) -1);
  }
  return $sHaystack;
}

/*
 * @return string the current task
 */
function current_task(){
  return task("current");
}

/*
 * @return string the last task
 */
function last_task(){
  return task("last");
}

/*
 * @param string $sWhichtask "current" or "last"
 * @return string the task defined by the given parameter
 */
function task($sWhichtask){
  global $sDatadir;
  $sPath = concat_path($sDatadir, $sWhichtask);

  if(!file_exists($sPath)){
    return null;
  }
  $aLines = file($sPath);
  $sLine = trim($aLines[0]);

  if(empty($sLine)){
    return null;
  }

  $aTasks = preg_split("/\t/", $sLine);
  $sStart = $aTasks[0];
  $sTask = $aTasks[1];
  $tEnd = time();
  $iMinutes = floor(($tEnd - $sStart) / 60);

  return array("start" => $sStart, "task" => $sTask, "end" => $tEnd, "minutes" => $iMinutes);
}

/*
 * @param string $sTask the current task
 */
function set_current_task($sTask){
  global $sDatadir, $sLastpath, $sCurrentpath;
  if(file_exists($sLastpath)){
    unlink($sLastpath);
  }
  $fFile = fopen($sCurrentpath, "w");
  fwrite($fFile, time() . "\t" . $sTask);
  fclose($fFile);
}

/*
 * @param int $iMinutes
 * @return string a nice representation of the given amount of minutes
 */
function minutes_to_clock_string($iMinutes){
  return floor($iMinutes / 60) . ":" . nice_minutes($iMinutes % 60);
}

/*
 * @param int $iMinutes
 * @return string minutes with 0 as prefix when lower than 10
 */
function nice_minutes($iMinutes){
  if($iMinutes < 10){
    $iMinutes = "0" . $iMinutes;
  }
  return $iMinutes;
}

if(empty($sInput)){
  $sTask = current_task();
  if($sTask == null){
    echo "You're not working on anything\n";
    exit;
  }
  echo "In progress\t", minutes_to_clock_string($sTask["minutes"]), "\t", $sTask["task"], "\n";
  exit;
}

if(preg_match("/^(r|resume)$/", $sInput) == 1){
  $sLast = last_task();
  if($sLast == null){
    echo "No task to resume\n";
    exit;
  }
  set_current_task($sLast["task"]);
  echo "Resuming ", $sLast["task"], "\n";
  exit;
}

$sTask = current_task();

if($sTask != null){
  $sYearpath = concat_path($sDatadir, date("Y"));
  if(!file_exists($sYearpath)){
    mkdir($sYearpath);
  }
  $sTodaypath = concat_path($sYearpath, date("Y-m-d", $sTask["start"]) . ".txt");
  $fFile = fopen($sTodaypath, "a");
  $sFormat = "Y-m-d h:i";
  $sEntry = array(date($sFormat, $sTask["start"]), date($sFormat, $sTask["end"]), $sTask["task"], $sTask["minutes"]);
  fwrite($fFile, join("\t", $sEntry) . "\n");
  fclose($fFile);
  echo "Finished\t", minutes_to_clock_string($sTask["minutes"]), "\t", $sTask["task"], "\n";

  rename($sCurrentpath, $sLastpath);
}

if(preg_match("/^(d|done|stop|)$/", $sInput) == 0){
  set_current_task($sInput);
  echo "Started\tnow\t", $sInput, "\n";
}


?>