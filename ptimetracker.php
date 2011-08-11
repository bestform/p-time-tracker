<?php

date_default_timezone_set("Europe/Berlin");
$datadir = $_SERVER['HOME'] . "/.ptimetracker";
$lastpath = $datadir . "/last";
$currentpath = $datadir . "/current";

if(!file_exists($datadir)) {
  mkdir($datadir);
}

unset($argv[0]);
$input = join(" ", $argv);

function current_task(){
  return task("current");
}

function last_task(){
  return task("last");
}

function task($whichtask){
  global $datadir;
  $path = $datadir . "/" . $whichtask;

  if(!file_exists($path)){
    return null;
  }
  $lines = file($path);
  $line = trim($lines[0]);

  if(empty($line)){
    return null;
  }

  $taskarray = preg_split("/\t/", $line);
  $start = $taskarray[0];
  $task = $taskarray[1];
  $end = time();
  $minutes = floor(($end - $start) / 60);

  return array("start" => $start, "task" => $task, "end" => $end, "minutes" => $minutes);
}

function set_current_task($task){
  global $datadir, $lastpath, $currentpath;
  if(file_exists($lastpath)){
    unlink($lastpath);
  }
  $f = fopen($currentpath, "w");
  fwrite($f, time() . "\t" . $task);
  fclose($f);
}

function h_m($minutes){
  return floor($minutes / 60) . ":" . nice_minutes($minutes % 60);
}

function nice_minutes($minutes){
  if($minutes < 10){
    $minutes = "0" . $minutes;
  }
  return $minutes;
}

if(empty($input)){
  $task = current_task();
  if($task == null){
    echo "You're not working on anything\n";
    exit;
  }
  echo "In progress\t" . h_m($task["minutes"]) . "\t" . $task["task"] . "\n";
  exit;
}

if(preg_match("/^(r|resume)$/", $input) == 1){
  $last = last_task();
  if($last == null){
    echo "No task to resume\n";
    exit;
  }
  set_current_task($last["task"]);
  echo "Resuming " . $last["task"] . "\n";
  exit;
}

$task = current_task();

if($task != null){
  $yearpath = $datadir . "/" . date("Y");
  if(!file_exists($yearpath)){
    mkdir($yearpath);
  }
  $todaypath = $yearpath . "/" . date("Y-m-d", $task["start"]) . ".txt";
  $f = fopen($todaypath, "a");
  $format = "Y-m-d h:i";
  $entry = array(date($format, $task["start"]), date($format, $task["end"]), $task["task"], $task["minutes"]);
  fwrite($f, join("\t", $entry) . "\n");
  fclose($f);
  echo "Finished\t", h_m($task["minutes"]), "\t", $task["task"] . "\n";

  rename($currentpath, $lastpath);
}

if(preg_match("/^(d|done|stop|)$/", $input) == 0){
  set_current_task($input);
  echo "Started\tnow\t", $input . "\n";
}


?>